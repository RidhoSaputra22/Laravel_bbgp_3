package main

import (
	"context"
	"database/sql"
	"flag"
	"log"
	"runtime/debug"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

func main() {
	once := flag.Bool("once", false, "process one outbox batch")
	drain := flag.Bool("drain", false, "process outbox batches until the queue is empty")
	mode := flag.String("mode", "live", "live or shadow")
	rebuild := flag.String("rebuild", "", "targets, validators, or all")
	compare := flag.String("compare", "", "targets, validators, or all shadow documents")
	reset := flag.Bool("reset", false, "delete the selected collection before rebuild")
	retryFailed := flag.Bool("retry-failed", false, "make failed outbox rows pending")
	showVersion := flag.Bool("version", false, "print engine version and hash")
	flag.Parse()
	if *once && *drain {
		log.Fatal("--once and --drain cannot be used together")
	}
	if *reset && !*drain && *rebuild == "" {
		log.Fatal("--reset requires --drain or --rebuild")
	}
	if *showVersion {
		log.Printf("sync-worker %s", engineVersionLine())
		return
	}

	config, err := loadConfig()
	if err != nil {
		log.Fatal(err)
	}
	if !*once && !*drain && *rebuild == "" && !*retryFailed {
		once = boolPtr(true)
	}
	if *mode != "live" && *mode != "shadow" {
		log.Fatal("--mode must be live or shadow")
	}
	debug.SetMemoryLimit(config.MemoryLimitBytes)
	log.Printf("sync-worker starting %s mode=%s batch_size=%d memory_limit=%dMiB max_runtime=%s", engineVersionLine(), *mode, config.BatchSize, config.MemoryLimitBytes/(1024*1024), config.MaxRuntime)
	startedAt := time.Now()
	defer func() {
		log.Printf("sync-worker finished duration=%s", time.Since(startedAt).Round(time.Millisecond))
	}()

	db, err := sql.Open("mysql", config.MySQLDSN)
	if err != nil {
		log.Fatal(err)
	}
	defer db.Close()
	ctx, cancel := context.WithTimeout(context.Background(), config.MaxRuntime)
	defer cancel()
	if err := db.PingContext(ctx); err != nil {
		log.Fatal(err)
	}
	log.Printf("mysql connected")
	if *retryFailed {
		result, err := db.ExecContext(ctx, `UPDATE sync_outbox SET status='pending', attempts=0, available_at=UTC_TIMESTAMP(), last_error=NULL, updated_at=UTC_TIMESTAMP() WHERE status='failed'`)
		if err != nil {
			log.Fatal(err)
		}
		count, _ := result.RowsAffected()
		log.Printf("retry-failed complete rows=%d", count)
		return
	}

	mongo, disconnect, err := NewMongoWriter(ctx, config, *mode == "shadow")
	if err != nil {
		log.Fatal(err)
	}
	defer disconnect()
	if err := mongo.EnsureIndexes(ctx); err != nil {
		log.Fatal(err)
	}
	log.Printf("mongodb connected database=%s targets=%s validators=%s shadow=%t", config.MongoDatabase, config.TargetCollection, config.ValidatorCollection, *mode == "shadow")
	worker := newWorker(db, mongo, config)
	if *compare != "" {
		if *compare != "targets" && *compare != "validators" && *compare != "all" {
			log.Fatal("--compare must be targets, validators, or all")
		}
		if *compare == "targets" || *compare == "all" {
			if err := mongo.Compare(ctx, config.TargetCollection); err != nil {
				log.Fatal(err)
			}
		}
		if *compare == "validators" || *compare == "all" {
			if err := mongo.Compare(ctx, config.ValidatorCollection); err != nil {
				log.Fatal(err)
			}
		}
		return
	}
	if *rebuild != "" {
		if *rebuild != "targets" && *rebuild != "validators" && *rebuild != "all" {
			log.Fatal("--rebuild must be targets, validators, or all")
		}
		if err := worker.Rebuild(ctx, *rebuild, *reset); err != nil {
			log.Fatal(err)
		}
		log.Printf("rebuild complete kind=%s reset=%t", *rebuild, *reset)
		return
	}
	if *drain {
		if *reset {
			if err := worker.Rebuild(ctx, "all", true); err != nil {
				log.Fatal(err)
			}
			log.Printf("reset rebuild complete kind=all")
		}
		if err := worker.RunUntilEmpty(context.Background(), config.MaxRuntime); err != nil {
			log.Fatal(err)
		}
	}
	if *once {
		if _, err := worker.RunOnce(ctx); err != nil {
			log.Fatal(err)
		}
	}
}

func boolPtr(value bool) *bool { return &value }
