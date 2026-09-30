<p align="center">
  <a href="https://laravel.com/">
    <img src="https://laravel.com/img/logomark.min.svg" alt="laravel logo" width="75" height="75">
  </a>
  <a href="https://getstisla.com">
    <img src="https://avatars2.githubusercontent.com/u/45754626?s=75&v=4" alt="Stisla logo" width="75" height="75">
  </a>
</p>

<h1 align="center">Laravel Stisla</h1>

<span align="center">

**Laravel Stisla** is a Free Bootstrap Admin Template which will help you to speed up your project and design your own dashboard UI using Laravel blade templating engine.

</span>

<br>

<p align="center">
  <a href="https://getstisla.com">Homepage</a>
  •
  <a href="https://github.com/ookapratama/laravel-stisla-starter#quick-start">Getting Started</a>
  •
  <a href="https://demo.getstisla.com" target="_new">Demo</a>
  •
  <a href="https://getstisla.com/docs">Documentation</a>
  •
  <a href="https://getstisla.com/blog">Blog</a>
  •
  <a href="https://github.com/ookapratama/laravel-stisla-starter/issues">Issue</a>
</p>

<br>

[![Stisla Preview](https://camo.githubusercontent.com/2135e0f6544a7286a3412cdc3df32d47fc91b045/68747470733a2f2f692e6962622e636f2f3674646d6358302f323031382d31312d31312d31352d33352d676574737469736c612d636f6d2e706e67)](https://getstisla.com)

## Table of Contents

- [Table of Contents](#table-of-contents)
- [Quick start](#quick-start)
- [Assessment API](#assessment-api)
- [Assessment target MongoDB projection](#assessment-target-mongodb-projection)
- [License](#license)
- [Supports](#supports)

## Quick start

Several quick start options are available:

-   Clone the repo: `git clone https://github.com/edikurniawan-dev/laravel-stisla.git`
-   Run `cd` to the newly created `/laravel-stisla` directory
-   Run `composer install` command
-   Run `npm install` command
-   Run `npx mix` command
-   Run `cp .env.example .env` command
-   Run `php artisan key:generate` command
-   Run `php artisan serve` command
-   Done

Read the [documentation page](https://getstisla.com/docs) for more information on the framework contents, templates and examples, and more.

## Assessment API

Obtain a Bearer token first:

```bash
API_BASE_URL="http://127.0.0.1:8000"

curl --fail-with-body --request POST "$API_BASE_URL/api/v1/auth/token" \
  --header 'Accept: application/json' \
  --header 'Content-Type: application/json' \
  --data '{"username":"USERNAME","password":"PASSWORD","device_name":"curl"}'
```

Use the returned `access_token` to retrieve the published assessment and save the JSON response:

```bash
API_TOKEN="PASTE_ACCESS_TOKEN_HERE"

curl --fail-with-body "$API_BASE_URL/api/v1/assessments/ASM-EVAL-PELAKSANAAN-HBG-001" \
  --header 'Accept: application/json' \
  --header "Authorization: Bearer $API_TOKEN" \
  --output assessment-evaluasi-pelaksanaan.json
```

The assessment endpoint accepts the assessment code or slug. The token must have the `assessment:read` ability.

## Assessment target MongoDB projection

MySQL remains the source of truth. When enabled, each row in
`assessment_assignment_targets` is asynchronously upserted as one document in
MongoDB for the JavaScript application.

Requirements:

- PHP 8.1+ with `ext-mongodb` 1.20 or newer, but below 2.0.
- The `mongodb/mongodb` Composer package at `~1.20.0`.
- A running MongoDB instance.
- A queue worker using the `database` connection.

On cPanel, assign this application to its own domain or subdomain in
**MultiPHP Manager**, select a PHP runtime that provides `ext-mongodb` 1.x,
and enable PHP-FPM for that domain. Do not replace the MongoDB extension in a
shared PHP runtime, as that can affect other applications on the account or
server. Confirm the web runtime (not only the terminal PHP) with:

```bash
php -r 'echo PHP_VERSION, " ", phpversion("mongodb"), PHP_EOL;'
composer check-platform-reqs
```

The expected MongoDB extension version is `1.x`. If cPanel does not offer a
PHP runtime with that extension, the hosting provider must install a separate
PHP runtime or the application must run in an isolated container.

For local development, this repository includes a project-only PHP runtime
wrapper. It uses the installed PHP 8.3 binary and loads MongoDB driver 1.20
from `.runtime`, without changing the global PHP installation:

```bash
bin/setup-project-runtime
bin/project-composer install --no-interaction
bin/project-serve
```

Use `bin/project-php artisan ...` for other Artisan commands and
`bin/project-composer ...` for Composer commands. Set `PROJECT_PHP_BIN` if
PHP 8.3 is installed at a different path.

Configure these values in `.env`:

```env
MONGODB_SYNC_ENABLED=false
MONGODB_URI=mongodb://127.0.0.1:27017
MONGODB_DATABASE=quiz_bbgtk
MONGODB_ASSIGNMENT_COLLECTION=assessment-assignment
MONGODB_SYNC_BATCH_SIZE=100
```

Preview and run an idempotent backfill:

```bash
php artisan assessment:sync-targets-mongodb --dry-run
php artisan assessment:sync-targets-mongodb --chunk=100
php artisan queue:work database --queue=default
```

The backfill command displays a `processed/total` progress bar and percentage.
Failed batches still advance the bar and are reported in the final summary.

To rebuild the collection from scratch and remove documents from an older
schema or previous source, use the explicit reset flag. It is intentionally
not performed by queue jobs, because queue jobs process individual targets:

```bash
php artisan assessment:sync-targets-mongodb --reset --force --chunk=100
```

`--reset` cannot be combined with `--assignment`, and it preserves MongoDB
indexes while deleting all existing documents. Without `--reset`, the stable
`_id` (`assessment-target:{target_id}`) and upsert operation prevent duplicate
documents.

Set `MONGODB_SYNC_ENABLED=true` only after the extension, MongoDB, and queue
worker are ready. The API assignment endpoints remain available for validation
and fallback.

The sync worker loads only the assignment combination snapshot when it is
available; the full assessment/form tree is loaded lazily only for assignments
that need the fallback schema. The MongoDB client is reused by long-lived queue
workers. Restart workers after changing code or `.env`:

```bash
php artisan queue:restart
```

## License

**Stisla** is licensed under the [MIT License](LICENSE)

## Supports

Thanks to BrowserStack for their support on this open-source project!

<a href="https://www.browserstack.com">
  <img src="https://getstisla.com/svg/Browserstack-logo.svg" alt="BrowserStack" width="250">
</a>

---

Stisla is created by [Nauval](http://nauv.al) ([Twitter](https://twitter.com/mhdnauvalazhar)). You can support the author by donation [here](https://www.buymeacoffee.com/mhd).
test
