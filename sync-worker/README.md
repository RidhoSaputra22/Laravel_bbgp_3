# BBPG MongoDB sync worker

Worker one-shot untuk sinkronisasi MySQL ke MongoDB. Worker membaca tabel
`sync_outbox`, membangun ulang snapshot target/validator dari source MySQL, lalu
menulis bulk upsert yang idempotent ke MongoDB.

## Build

```bash
cd sync-worker
go mod download
go test ./...
cd ..
bash bin/build-sync-worker
storage/app/bbpg-sync-worker --version
```

Build menyuntikkan versi Git, commit hash penuh, dan waktu build ke binary.
Metadata yang sama ikut ditulis ke `sync.engine_schema_version`,
`sync.engine_name`, `sync.engine_version`, `sync.engine_hash`, dan
`sync.engine_build_time` pada setiap dokumen target/validator. Jika binary
dibangun tanpa `bin/build-sync-worker`, Go tetap mencoba membaca metadata Git
dari build info; gunakan script tersebut untuk hasil yang eksplisit di
hosting.

Pemeriksaan statis dan race detector:

```bash
go test -race ./...
go vet ./...
```

Integration test MySQL dan MongoDB bersifat opt-in. Gunakan database khusus
testing yang sudah menjalankan migration; test akan membuat data sementara
dan membersihkannya kembali. Jangan gunakan kredensial database production.

```bash
SYNC_INTEGRATION_TESTS=1 \
SYNC_TEST_MYSQL_DSN='user:password@tcp(127.0.0.1:3306)/sync_worker_test?parseTime=true' \
SYNC_TEST_MONGO_URI='mongodb://127.0.0.1:27017' \
SYNC_TEST_MONGO_DATABASE='sync_worker_test' \
go test ./... -run Integration -count=1
```

Integration test memverifikasi claim/complete/retry/fail outbox, penggantian
dokumen MongoDB, collection shadow, dan reset rebuild. Tanpa
`SYNC_INTEGRATION_TESTS=1`, test tersebut otomatis dilewati.

Worker dapat memakai `SYNC_MYSQL_DSN`. Jika kosong, worker membentuk DSN dari
`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.

Worker memakai batas memory Go lunak melalui `SYNC_MEMORY_LIMIT_MB` (default
`1`). Nilai ini bukan hard limit OS; 1 MiB sangat kecil untuk runtime Go dan
driver database, sehingga gunakan nilai lebih besar jika worker berjalan lambat
atau gagal memproses batch.

## Mode

Laravel memakai `MONGODB_SYNC_DRIVER=php` secara default.

- `php`: hanya job Laravel lama.
- `dual`: job Laravel menulis collection production; worker Go dijalankan dengan
  `--mode=shadow` dan menulis collection dengan suffix `_go_shadow`.
- `go`: event hanya masuk outbox dan worker Go menulis collection production.

Jalankan satu batch:

```bash
storage/app/bbpg-sync-worker --once
```

Jalankan langsung di terminal sampai seluruh outbox kosong:

```bash
bash bin/sync-worker --drain
```

Mode `--drain` memproses batch berulang sampai tidak ada event pending, lalu
keluar. Terminal menampilkan satu progress bar yang diperbarui setiap batch.
Batas waktu `SYNC_MAX_RUNTIME_SECONDS` berlaku per batch.

Perintah tersebut bersifat one-shot: setelah satu batch selesai worker keluar.
Worker sekarang mencetak status `starting` beserta versi/hash engine, koneksi
MySQL/MongoDB, `outbox idle` bila tidak ada event, jumlah event yang diproses,
dan `finished`.

Mode shadow:

```bash
storage/app/bbpg-sync-worker --once --mode=shadow
```

Bandingkan dokumen production dan shadow setelah canary:

```bash
storage/app/bbpg-sync-worker --compare=all
```

Rebuild eksplisit:

```bash
storage/app/bbpg-sync-worker --rebuild=targets --reset
storage/app/bbpg-sync-worker --rebuild=validators --reset
```

`--reset` hanya boleh digunakan pada rebuild yang memang disengaja. Jangan
menjalankan worker Go dan job Laravel production secara bersamaan setelah mode
`go` diaktifkan.

## Cron

Gunakan lock lokal agar cron berikutnya tidak overlap:

```cron
* * * * * cd /path/to/Laravel && flock -n storage/app/bbpg-sync-worker.lock bash bin/sync-worker --once >> storage/logs/bbpg-sync-worker.log 2>&1
```

Log cron tersimpan di `storage/logs/bbpg-sync-worker.log`. Untuk melihatnya:

```bash
tail -f storage/logs/bbpg-sync-worker.log
```

Untuk mode `dual`, ganti `--once` dengan `--once --mode=shadow`.
