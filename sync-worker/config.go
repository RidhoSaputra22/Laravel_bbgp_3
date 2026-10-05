package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
	"time"
)

type Config struct {
	MySQLDSN            string
	MongoURI            string
	MongoDatabase       string
	TargetCollection    string
	ValidatorCollection string
	ShadowSuffix        string
	BatchSize           int
	BuildConcurrency    int
	MemoryLimitBytes    int64
	MaxRuntime          time.Duration
	LeaseTimeout        time.Duration
	MaxAttempts         int
}

func loadConfig() (Config, error) {
	c := Config{
		MySQLDSN:            env("SYNC_MYSQL_DSN", ""),
		MongoURI:            env("SYNC_MONGODB_URI", env("MONGODB_URI", "mongodb://127.0.0.1:27017")),
		MongoDatabase:       env("SYNC_MONGODB_DATABASE", env("MONGODB_DATABASE", "quiz_bbgtk")),
		TargetCollection:    env("SYNC_TARGET_COLLECTION", env("MONGODB_ASSIGNMENT_COLLECTION", "assessment_assignment")),
		ValidatorCollection: env("SYNC_VALIDATOR_COLLECTION", env("MONGODB_VALIDATOR_ASSIGNMENT_COLLECTION", "validator_assignment")),
		ShadowSuffix:        env("SYNC_SHADOW_SUFFIX", "_go_shadow"),
		BatchSize:           envInt("SYNC_BATCH_SIZE", 100),
		BuildConcurrency:    envInt("SYNC_BUILD_CONCURRENCY", 4),
		MemoryLimitBytes:    int64(envInt("SYNC_MEMORY_LIMIT_MB", 1)) * 1024 * 1024,
		MaxRuntime:          time.Duration(envInt("SYNC_MAX_RUNTIME_SECONDS", 50)) * time.Second,
		LeaseTimeout:        time.Duration(envInt("SYNC_LOCK_TIMEOUT_SECONDS", 600)) * time.Second,
		MaxAttempts:         envInt("SYNC_MAX_ATTEMPTS", 10),
	}

	if c.MySQLDSN == "" {
		c.MySQLDSN = mysqlDSNFromLaravelEnv()
	}
	if c.MySQLDSN != "" {
		c.MySQLDSN = withUTCMySQLSession(c.MySQLDSN)
	}
	if c.MySQLDSN == "" {
		return Config{}, fmt.Errorf("SYNC_MYSQL_DSN is required (or provide DB_HOST/DB_DATABASE/DB_USERNAME)")
	}
	if c.BatchSize < 1 || c.BuildConcurrency < 1 || c.MemoryLimitBytes < 1 || c.MaxAttempts < 1 {
		return Config{}, fmt.Errorf("SYNC_BATCH_SIZE, SYNC_BUILD_CONCURRENCY, SYNC_MEMORY_LIMIT_MB, and SYNC_MAX_ATTEMPTS must be positive")
	}
	return c, nil
}

func env(key, fallback string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return fallback
}

func envInt(key string, fallback int) int {
	value, err := strconv.Atoi(os.Getenv(key))
	if err != nil || value == 0 {
		return fallback
	}
	return value
}

func mysqlDSNFromLaravelEnv() string {
	host := env("DB_HOST", "127.0.0.1")
	port := env("DB_PORT", "3306")
	database := os.Getenv("DB_DATABASE")
	username := os.Getenv("DB_USERNAME")
	password := os.Getenv("DB_PASSWORD")
	if database == "" || username == "" {
		return ""
	}
	return fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true&charset=utf8mb4&loc=UTC", username, password, host, port, database)
}

func withUTCMySQLSession(dsn string) string {
	separator := "?"
	if strings.Contains(dsn, "?") {
		separator = "&"
	}

	// `loc=UTC` only controls Go time.Time values. This DSN system variable
	// sets every MySQL connection's session clock to the same UTC basis used by
	// UTC_TIMESTAMP() and the outbox timestamps.
	return dsn + separator + "time_zone=%27%2B00%3A00%27"
}
