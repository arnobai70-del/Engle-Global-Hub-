# Production Deployment Checklist

This checklist prepares a reviewed release without activating optional travel
providers or live flight order execution. Start from
`.env.production.example`, but inject completed values through the deployment
platform's encrypted environment or secret store. Never commit the real `.env`.

## Release invariants

- Deploy an immutable artifact from the reviewed commit.
- Keep `HOTELS_ENABLED`, `TOURS_ENABLED`, and `VISA_ENABLED` set to `false`
  until each provider is separately approved and configured.
- Keep `FLIGHT_ORDER_HTTP_EXECUTION_ENABLED` and
  `DUFFEL_LIVE_ORDER_CREATION_ENABLED` set to `false` until live order and
  payment operations are explicitly approved.
- Never put database passwords, mail credentials, API keys, or supplier tokens
  in the repository, artifact, command output, or deployment record.

## Runtime requirements

- PHP `^8.3`, Composer, and the PHP extensions reported by
  `composer check-platform-reqs --no-dev`. The current dependency set uses
  ctype, date, DOM, fileinfo, filter, hash, iconv, JSON, libxml, OpenSSL, PCRE,
  session, and tokenizer. PDO plus the selected production database driver,
  such as `pdo_mysql`, is also required.
- Vite 8 requires Node `^20.19.0` or `>=22.12.0`. `package.json` does not pin an
  npm version; use the npm release bundled with a supported Node runtime. This
  checkpoint was validated with Node 24 and npm 11.
- The web server document root must be the repository's `public` directory.
- The web/PHP process must be able to write `storage` and `bootstrap/cache`.
  Source and configuration files should otherwise be read-only to that process.
- **`php artisan storage:link` is required.** Team photos, the media library,
  the office gallery, destination images and content covers are stored on the
  `public` disk. Student documents and payment receipts use the private
  `student_documents` and `payment_receipts` disks and are never linked or
  served directly.

## Infrastructure inputs

Before deploying, provision:

1. The production domain and valid TLS certificate.
2. A durable database and a least-privilege application database user. Create
   the database outside Laravel; do not use SQLite merely to satisfy readiness.
3. A real mail delivery transport and verified sender address.
4. Persistent session/cache storage and an asynchronous queue connection.
5. Centralized logs, monitoring, alerting, encrypted secret storage, and
   automated database backups with a tested restore procedure.

**A queue worker and the scheduler are required.** Student, appointment,
document-review, message and enquiry notifications are queued (`ShouldQueue`,
dispatched after commit). Without a supervised `php artisan queue:work` process
they are never sent. Admin › API & Integrations shows a "Queue worker" warning
when jobs wait longer than 10 minutes or have failed.

The scheduler runs daily housekeeping (purging expired API tokens and expired
password-reset tokens) and weekly failed-job pruning. It needs a cron entry
running `php artisan schedule:run` every minute. See `docs/DEPLOYMENT.md`.

## Validation before release

On a clean CI or release checkout, run tests and build assets before packaging:

```powershell
composer install --prefer-dist --no-interaction
php artisan test
npm ci
npm run build
composer check-platform-reqs --no-dev
```

Review pending migrations and their rollback implications before approving the
release. Exclude `.env`, `.git`, `node_modules`, tests, local logs, local cache
files, and developer databases from the production artifact.

## Deployment order

After the production environment has been injected, run the repository's
required deployment steps in this order:

```powershell
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force
php artisan app:production-readiness
```

Run **only** `ProductionSeeder` on a server with live data. It never overwrites
live records and never publishes content. Never run `DatabaseSeeder` (test user),
`HomepageContentSeeder`, `CountrySeeder` or `CitySeeder` on production. The
Contabo-specific commands (release folders, Supervisor, cron, staging over an
SSH tunnel) are in `docs/DEPLOYMENT.md`.

Every readiness check must report `PASS` or an understood non-blocking `WARN`;
any `FAIL` blocks release. The command performs only local configuration,
filesystem, asset and read-only database/migration checks. It does not contact
travel providers, reveal credential values, or change state.

If the deployment artifact already contains reviewed frontend assets,
`npm ci` and `npm run build` belong in the artifact-build stage instead
of the production host. Do not rebuild an immutable artifact after approval.

On Windows PowerShell hosts where script execution blocks `npm.ps1`, use
`npm.cmd` for the same npm commands.

## Smoke verification

- Confirm `GET /up` returns HTTP 200 through the production load balancer. It is
  a coarse liveness response, not a public diagnostic endpoint.
- Confirm home, authentication, account, booking, invoice and public pages
  render over HTTPS without mixed-content or console errors.
- Confirm hotels, tours and visa show as unconfigured, and verify no live flight
  supplier order can be initiated while the safety flags are false.
- Send a controlled verification email through the configured mail transport.
- Check application/infrastructure logs and monitoring without exposing them to
  public traffic.

Record the commit SHA, artifact identifier, deployment time, operator,
readiness result, migration result, smoke-test result, backup reference, and
rollback decision in the private release record.

## Backup and rollback boundary

Create and verify a restorable database backup before migrations
(`docs/BACKUP_RESTORE.md` §2). Determine
whether every migration is backward compatible with the previous application
release before switching traffic.

Application rollback means switching traffic to the previous immutable
artifact. Do not run `migrate:rollback` automatically and do not delete current
data. If a migration is not backward compatible, stop and follow its reviewed
forward-fix or restore plan. Full procedure: `docs/ROLLBACK_PLAN.md`.

## Contabo go-live prerequisites (15 Nov 2026)

| Prerequisite | Status |
|---|---|
| Server identified (Contabo VPS, Apache 2.4.52, Ubuntu), document root `public/`, HTTPS with Let's Encrypt | READY (from public checks) |
| Read-only server audit output (`docs/DEPLOYMENT.md` §2): PHP version and extensions, database engine, disk, firewall, layout | BLOCKED (needs SSH output from the owner) |
| Migrations tested on MySQL/MariaDB (staging) | BLOCKED (no MySQL available locally; needs staging) |
| Contabo snapshot before release | READY (`before-release-2026-11-15`, 15 Nov 2026; retake right before the actual deployment) |
| Pre-release encrypted database and files backup | NEEDS CONFIGURATION |
| Webmin restricted (port 10000 is public), ufw enabled for 22/80/443 | REQUIRES OWNER APPROVAL (security) |
| Off-server backup target and retention | REQUIRES OWNER APPROVAL (choose and pay for storage) |
| Restore rehearsal | BLOCKED (needs staging) |
| Outgoing email (SMTP provider, SPF/DKIM/DMARC) | REQUIRES OWNER APPROVAL (provider choice and DNS change) |
| Supervisor queue worker | REQUIRES OWNER APPROVAL (server package and config) |
| Scheduler cron | REQUIRES OWNER APPROVAL (server crontab) |
| `storage:link` and storage permissions | NEEDS CONFIGURATION |
| Apache security headers and `ServerTokens Prod` | REQUIRES OWNER APPROVAL (server config) |
| Production `.env` keeps the live APP_KEY; `APP_ENV`, `APP_DEBUG`, `LOG_LEVEL`, secure cookies verified | NEEDS CONFIGURATION (confirm with the audit) |
| Commit, review and push the release | REQUIRES OWNER APPROVAL |
| Deployment | REQUIRES OWNER APPROVAL |