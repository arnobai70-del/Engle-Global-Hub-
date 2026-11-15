# Deployment Guide (Contabo)

How to release Eagle Global Hub to the existing Contabo server without interrupting the live site or losing data.

- Release rules (immutable artifacts, provider safety flags, secrets): `docs/PRODUCTION_DEPLOYMENT_CHECKLIST.md`
- Backups: `docs/BACKUP_RESTORE.md`
- Rollback: `docs/ROLLBACK_PLAN.md`
- Status and blockers: `docs/PRODUCTION_READINESS.md`

Nothing in this repository deploys automatically. Deploy only a reviewed, committed release, and only after separate owner approval.

## 1. What is known about the live server

These facts come from public, read-only checks run on 15 Nov 2026 (DNS, TLS and HTTP responses). Nobody logged in to the server.

| Item | Finding | How it was determined |
|---|---|---|
| Provider and server | Contabo **Cloud VPS 4** (4 vCPU, 8 GB RAM, 100 GB disk), `vmi3709944.contaboserver.net`, IP 173.212.237.174 | Contabo panel (owner) and reverse DNS |
| Image / control panel | **Webmin-LAMP** image: Webmin, plus Apache, a MySQL-family database and PHP | Contabo panel (owner) |
| Contabo firewall | **None** configured | Contabo panel (owner) |
| Contabo Auto Backup | **Not subscribed**. Manual snapshot `before-release-2026-11-15` taken on 15 Nov 2026 | Contabo panel (owner) |
| Open ports (from the internet) | **22 (SSH), 80, 443, 10000 (Webmin)** open. 21, 25, 587, 3306, 5432, 6379, 8080, 8443 and 20000 closed or filtered | TCP connect test, 15 Nov 2026 |
| Webmin | **Login page publicly reachable** at `https://173.212.237.174:10000` (MiniServ, default self-signed certificate `CN=example`). Version not visible | HTTP headers and page title only; no login attempted |
| ufw (server firewall) | **Unknown**: needs `sudo ufw status verbose` on the server. Port 10000 being open means either ufw is off or it allows 10000 | Cannot be determined from outside |
| PHP | Very likely 8.3+: the live release's `composer.json` already requires `^8.3` | Repository history. **Confirm** with `php -v` |
| Operating system | Ubuntu, most likely 22.04 LTS | `Server: Apache/2.4.52 (Ubuntu)` is the Ubuntu 22.04 Apache build. **Confirm** |
| Web server | Apache 2.4.52, document root is Laravel's `public/` | `.env`, `.git`, `composer.json`, `artisan`, logs and the database path all return 404 |
| Application | This Laravel application, an **older release** than the current `main` | Session cookie `eagle-global-hub-ltd-session`. Routes from PR #31 and all new modules return 404 |
| HTTPS | Let's Encrypt certificate for `eagleglobalhub.com` and `www`, valid until 28 Jan 2027. HTTP redirects to HTTPS | TLS handshake |
| Cookies | Already `secure`, `httponly`, `samesite=lax` | Response headers |
| Security headers | **None**: no HSTS, X-Content-Type-Options, X-Frame-Options or Referrer-Policy. The Apache version is disclosed | Response headers |
| `www` | Serves the site directly (200) instead of redirecting to the main domain | HTTP check |
| DNS | Namecheap nameservers (`registrar-servers.com`) | NS records |
| Email | **Receiving only**: Namecheap email forwarding (`eforward*` MX, SPF for forwarding). No outgoing mail service, no DKIM, no DMARC | MX/TXT records |
| Live robots.txt | `Disallow:` (allows everything) and a relative sitemap URL. The new release fixes both | HTTP check |
| `/storage/` | 403 (directory listing disabled). Whether the storage link exists is unknown | HTTP check |

### Webmin and firewall (security: high priority, owner approval required)

Webmin runs as **root**. A public login page on port 10000 can be brute-forced, and old Webmin versions had remote-code-execution flaws (for example CVE-2019-15107). With no Contabo firewall and ufw status unknown, nothing filters it today.

Read-only checks (run over SSH; they change nothing):

```bash
sudo ufw status verbose                          # "Status: inactive" means no host firewall
sudo iptables -S | head -30; sudo nft list ruleset 2>/dev/null | head -30
sudo ss -tlnp | grep -E ':10000|:3306|:22 '     # which services listen on which addresses
dpkg -l webmin | tail -1                         # installed Webmin version
grep -E '^(port|ssl|allow|deny|twofactor_provider|bind)=' /etc/webmin/miniserv.conf
sudo grep -cE 'Invalid login|Failed login' /var/webmin/miniserv.error 2>/dev/null
sudo grep -E '^(PermitRootLogin|PasswordAuthentication|Port)' /etc/ssh/sshd_config
systemctl is-active fail2ban 2>/dev/null
```

Recommended changes, **each needing owner approval**. Do them in this order so access is never lost:

1. **Restrict Webmin to the owner only.** Either:
   - add `allow=<owner's fixed IP>` to `/etc/webmin/miniserv.conf`, then `sudo systemctl restart webmin`; or
   - better, `bind=127.0.0.1` and use Webmin only through an SSH tunnel (`ssh -L 10000:127.0.0.1:10000 user@173.212.237.174`, then open `https://127.0.0.1:10000`).
2. **Update Webmin** (`sudo apt update && sudo apt install --only-upgrade webmin`) and turn on two-factor login in Webmin › Webmin Configuration › Two-Factor Authentication.
3. **Enable ufw** with exactly these rules, **SSH first**:
   ```bash
   sudo ufw allow 22/tcp      # or the custom SSH port shown by the check above
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw default deny incoming
   sudo ufw enable
   ```
   Keep an existing SSH session open while enabling, and test a **new** SSH login before closing it. The Contabo panel's VNC console is the recovery path if SSH is locked out.
4. Optional: also create a matching Contabo firewall in the panel (22/80/443 only), and install `fail2ban` for SSH.
5. SSH: key-only login (`PasswordAuthentication no`) and `PermitRootLogin prohibit-password`, after confirming the owner's key works.

None of this changes the website's appearance. Ports 80 and 443 stay open.

**Unknown until the read-only audit in section 2:**
- control panel (if any)
- PHP version and extensions
- database engine and size
- disk space
- firewall
- installed services (Supervisor, cron, Redis)
- the current deployment layout (path, Git or uploads)
- whether a queue worker or scheduler runs today
- existing backups (including Contabo snapshots)

## 2. Read-only server audit (owner runs, sends the output)

Run as the server's admin user over SSH. Every command only **reads**. Nothing is installed, restarted or changed.

The script prints only the **names** of `.env` keys plus a few non-secret values; it never prints passwords or keys. Check the output before sending it.

```bash
set -u
echo "== OS / resources";  lsb_release -ds 2>/dev/null || cat /etc/os-release; uname -r; nproc; free -h; df -h /; uptime
echo "== Control panel";   ls -d /usr/local/cpanel /usr/local/psa /usr/local/hestia /usr/local/vesta /usr/local/CyberCP /opt/webuzo 2>/dev/null || echo "none found"
echo "== Web server";      apache2 -v 2>/dev/null; nginx -v 2>&1 | head -1; apache2ctl -S 2>/dev/null | head -30
echo "== Site config";     grep -RhoE "DocumentRoot\s+\S+|ServerName\s+\S+|ServerAlias.*" /etc/apache2/sites-enabled/ 2>/dev/null | sort -u
echo "== PHP";             php -v | head -1; php -m | tr '\n' ' '; echo; ls /etc/php/ 2>/dev/null; php -i | grep -E "^(memory_limit|upload_max_filesize|post_max_size|max_execution_time|opcache.enable) " 
echo "== Tools";           composer --version 2>/dev/null; node -v 2>/dev/null; npm -v 2>/dev/null; git --version
echo "== Database";        mysql --version 2>/dev/null; mariadb --version 2>/dev/null; systemctl is-active mysql mariadb postgresql redis-server 2>/dev/null
echo "== Services";        systemctl is-active supervisor cron apache2 2>/dev/null; ls /etc/supervisor/conf.d/ 2>/dev/null; ls /etc/systemd/system | grep -iE "queue|laravel|horizon|worker" 
echo "== Cron";            crontab -l 2>/dev/null; sudo crontab -l -u www-data 2>/dev/null; ls /etc/cron.d/
echo "== Firewall";        sudo ufw status verbose 2>/dev/null | head -20; sudo ss -tlnp | awk '{print $4, $6}' | head -30
echo "== TLS";             sudo certbot certificates 2>/dev/null | grep -E "Certificate Name|Domains|Expiry"; systemctl list-timers | grep -i certbot
```

Then set `APP_DIR` to the `DocumentRoot` shown above **without** the trailing `/public`, and run:

```bash
APP_DIR=/path/to/app
cd "$APP_DIR"
echo "== Layout";      pwd -P; ls -la; readlink -f public; git -C "$APP_DIR" log --oneline -1 2>/dev/null; git -C "$APP_DIR" status --short 2>/dev/null | wc -l
echo "== Env keys";    grep -oE '^[A-Z0-9_]+=' .env | tr '\n' ' '; echo
echo "== Env values";  grep -E '^(APP_ENV|APP_DEBUG|APP_URL|LOG_CHANNEL|LOG_LEVEL|DB_CONNECTION|SESSION_DRIVER|SESSION_SECURE_COOKIE|CACHE_STORE|QUEUE_CONNECTION|MAIL_MAILER|FILESYSTEM_DISK)=' .env
echo "== APP_KEY set"; grep -qE '^APP_KEY=base64:.{40,}' .env && echo yes || echo NO
echo "== Migrations";  php artisan migrate:status --no-ansi | tail -25
echo "== Readiness";   php artisan app:production-readiness --no-ansi 2>/dev/null || echo "command not in this release"
echo "== Storage";     ls -la public/storage 2>/dev/null; du -sh storage/app/* 2>/dev/null; sudo -u www-data test -w storage && echo "storage writable"
echo "== Owners";      stat -c '%U:%G %a %n' . storage bootstrap/cache .env
echo "== DB size";     php artisan db:show --no-ansi 2>/dev/null | head -20
echo "== Queue";       php artisan tinker --execute 'echo "jobs=".DB::table("jobs")->count()." failed=".DB::table("failed_jobs")->count();' 2>/dev/null
echo "== Data counts"; php artisan tinker --execute 'foreach (["users","agents","students","flight_order_attempts","newsletter_subscribers","homepage_blocks","settings"] as $t) { echo $t."=".(Schema::hasTable($t) ? DB::table($t)->count() : "absent")." "; }' 2>/dev/null
```

Also tell us:
- the Contabo product (VPS size)
- whether Contabo **backup or snapshot** add-ons are active
- who has SSH access
- whether anything else (other sites, mail server) runs on this VPS

## 3. Target architecture on the existing server

The same server, the same Apache, and the same database and storage as today. The release **upgrades the live application in place**. It does not replace it with a separate system.

- **Database:** the live database is kept. The 30 new migrations only add tables and nullable or defaulted columns (section 6), so existing records are untouched.
- **Files:** the live `storage/` (uploads, private folders) and `.env` (including the existing **APP_KEY**, which must never be regenerated) are kept.
- **Code layout:** recommended for zero-downtime releases and one-command rollback:
  ```
  /var/www/eagleglobalhub/
    releases/<commit-sha>/     # one folder per release (code + vendor + public/build)
    shared/.env                # the existing live .env, moved here once
    shared/storage/            # the existing live storage/, moved here once
    current -> releases/<sha>  # Apache DocumentRoot = /var/www/eagleglobalhub/current/public
  ```
  Moving to this layout changes the Apache `DocumentRoot` once, so it needs **owner approval**. If the owner prefers to keep the current single folder, use the in-place steps in section 5b. Rollback then means restoring the previous code archive (see `docs/ROLLBACK_PLAN.md`).
- **Queue:** one worker supervised by Supervisor (or systemd), section 7.
- **Scheduler:** one cron line, section 8.
- **Email:** an external SMTP or transactional service (section 9). Namecheap forwarding cannot send.

### Staging without touching the live site

Staging runs on the same VPS, but only on `127.0.0.1`. You reach it through an SSH tunnel, so **no DNS, firewall or public Apache change** is needed.

1. Copy the release to `/var/www/egh-staging`, with its own `.env`:
   - `APP_ENV=staging`
   - `APP_URL=http://127.0.0.1:8081`
   - a **separate database** `egh_staging`, restored from the latest live backup
   - `MAIL_MAILER=log`, so no email can reach real students
   - `SESSION_SECURE_COOKIE=false` (HTTP over the tunnel only)
   - all provider flags `false`
2. Serve it with `php artisan serve --host=127.0.0.1 --port=8081`. Run this in `tmux`, stop it after testing, and never use it for production.
3. From your computer: `ssh -L 8081:127.0.0.1:8081 <user>@173.212.237.174`, then open `http://127.0.0.1:8081`.
4. On staging, run the migrations, the `ProductionSeeder`, the readiness command and the smoke checks (section 10). Confirm live records (users, agents, bookings) are intact and new modules work.
5. Delete the staging database copy afterwards. It contains real personal data.

## 4. Server requirements (to confirm with the audit)

- PHP **8.3** (`composer.json` requires `^8.3`) with OPcache.
- PHP extensions: ctype, curl, dom, fileinfo, filter, hash, iconv, json, libxml, mbstring, openssl, pcre, pdo_mysql (or the live database's driver), session, tokenizer, xml. Check with `composer check-platform-reqs --no-dev` on the server.
- Composer 2. Node is **not** needed on the server if the release is built elsewhere (recommended: build `public/build` in CI or locally from the reviewed commit).
- MySQL 8 or MariaDB 10.6+. The test suite runs on SQLite; the migrations have **not yet been run on MySQL**, so the staging run in section 3 is mandatory.
- Supervisor (`apt install supervisor`) for the queue. Installing it is a server change and needs **owner approval**.
- Disk: release size (vendor + build) is about 100–150 MB per release. Keep 3 releases, plus backups if stored locally before upload.
- PHP limits: `upload_max_filesize` and `post_max_size` at least **12M** (student documents up to 10 MB, homepage images up to 8 MB, other images 2–5 MB); `memory_limit` 256M.

## 5. Release steps

Run as the deploy user (not root). `<sha>` is the reviewed commit.

### 5a. Release-folder layout (recommended)

```bash
set -euo pipefail
BASE=/var/www/eagleglobalhub; REL=$BASE/releases/<sha>
# 1. Backup first (docs/BACKUP_RESTORE.md §2). Do not continue without a verified backup.
# 2. Upload the artifact (code + vendor + public/build, built from <sha>) into $REL
ln -sfn $BASE/shared/.env $REL/.env
rm -rf $REL/storage && ln -sfn $BASE/shared/storage $REL/storage
cd $REL
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist   # skip if vendor is in the artifact
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan down --render="errors::503" --retry=60     # on the CURRENT release (short window)
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force
ln -sfn $REL $BASE/current.new && mv -T $BASE/current.new $BASE/current   # atomic switch
sudo systemctl reload php8.3-fpm 2>/dev/null || sudo systemctl reload apache2   # clears OPcache
php artisan queue:restart
php artisan up
php artisan app:production-readiness
```

### 5b. In-place layout (if the owner keeps the current folder)

```bash
cd /path/to/app
# 1. Backup first, including a tar of the current code folder (docs/BACKUP_RESTORE.md §2)
php artisan down --render="errors::503" --retry=60
# 2. Replace code files from the artifact. Keep .env and storage/ untouched.
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force
php artisan storage:link
php artisan optimize        # config, route, view and event caches
sudo systemctl reload apache2
php artisan queue:restart
php artisan up
php artisan app:production-readiness
```

**Seeding rules:** run **only** `ProductionSeeder`.
- It merges role permissions, so permissions granted on the live site are kept.
- It adds missing countries without changing live ones.
- It creates settings, team, destination and content records only when missing, always unpublished.
- Never run `DatabaseSeeder` (it creates `test@example.com`), `HomepageContentSeeder` (it overwrites the live homepage), `CountrySeeder` or `CitySeeder` (they overwrite live edits).

## 6. Database migration risk review

- **30 new migrations, all additive.** They create new tables, and on existing tables they add nullable or defaulted columns:
  - `students.user_id` (nullable, unique)
  - `agents.partner_type` (default `agent`) and `agents.website_url` (nullable)
  - nullable columns on tables that are new in this release
- **No** drop, rename, `change()`, truncate or data update. No committed migration was edited.
- The live site's own migration `2026_11_07_000000_create_flight_enquiries_table` may also still be pending. It is additive.
- **Old code works with the new schema:** the previous release ignores new tables, and new columns are nullable or defaulted. A code rollback therefore does not require a database rollback.
- **Risks:**
  - MySQL has not been tested yet (run on staging first).
  - `students.user_id` gets a unique index on an empty new column, so it is safe.
  - Long-running `ALTER TABLE` statements are not expected at this data size; confirm with the audit's table counts.

## 7. Queue worker (required: notifications are queued)

Supervisor, `/etc/supervisor/conf.d/egh-queue.conf`. Adjust the path and user to the audit results:

```ini
[program:egh-queue]
command=/usr/bin/php /var/www/eagleglobalhub/current/artisan queue:work database --sleep=3 --tries=3 --backoff=60 --max-time=3600 --memory=256
user=www-data
numprocs=1
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
stopwaitsecs=120
redirect_stderr=true
stdout_logfile=/var/www/eagleglobalhub/shared/storage/logs/queue.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
```

Enable with `sudo supervisorctl reread && sudo supervisorctl update` (needs owner approval).

| Topic | Procedure |
|---|---|
| Restart after each release | `php artisan queue:restart`. Supervisor starts fresh workers. |
| Retry policy | 3 attempts, 60 s apart. Afterwards the job goes to `failed_jobs`. |
| Failed-job monitoring | Admin › API & Integrations shows "Needs attention" when jobs wait over 10 minutes or fail. `php artisan queue:failed` lists them; `php artisan queue:retry <id>` retries one. Failed jobs older than 30 days are pruned weekly. |
| systemd alternative | A `Restart=always` service running the same command, if Supervisor is not wanted. |

## 8. Scheduler (required)

The `www-data` crontab (`sudo crontab -u www-data -e`, which needs owner approval):

```cron
* * * * * cd /var/www/eagleglobalhub/current && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Tasks (`php artisan schedule:list`):
- daily: purge expired API tokens
- daily: clear expired password resets
- weekly: prune old failed jobs

## 9. Email

Today the domain can only **receive** mail (Namecheap forwarding). Sending needs a transactional or SMTP service chosen by the owner, for example Namecheap Private Email, Zoho Mail, Brevo, Mailgun or Amazon SES. Then:

1. Set `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT=587`, `MAIL_USERNAME`, `MAIL_PASSWORD` and `MAIL_FROM_ADDRESS` (for example `no-reply@eagleglobalhub.com`) in the server `.env` only.
2. Add the provider's SPF include and DKIM records, plus a DMARC record (start with `p=none`), at Namecheap. This is a **DNS change** and needs owner approval. The current SPF record must be **merged**, not replaced, so forwarding keeps working.
3. Check whether outgoing SMTP ports are open from the VPS (`nc -vz <smtp-host> 587`).
4. Test by sending **one** password-reset email to a staff mailbox. Never send test notifications to students.

## 10. Smoke checks after release

- `GET /up` returns 200.
- `/`, `/about`, `/study-in-singapore`, `/login`, `/sitemap.xml` and `/robots.txt` load over HTTPS. The homepage looks exactly as before.
- Existing users can sign in. Existing agents, bookings and enquiries are visible in admin.
- Admin › API & Integrations: email configured, queue worker healthy.
- `/admin` redirects guests to login. A student document URL returns 403 or 404 for another user.
- `/sitemap.xml` lists no drafts. `/content-approvals` is reachable only by the owner.
- `php artisan app:production-readiness` shows no FAIL.

## 11. Recommended Apache hardening (owner approval; no visible change)

Inside the site's `<VirtualHost *:443>`:

```apache
Header always set Strict-Transport-Security "max-age=31536000"
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
<DirectoryMatch "/\.(?!well-known)">
    Require all denied
</DirectoryMatch>
```

In `/etc/apache2/conf-enabled/security.conf`, set `ServerTokens Prod` and `ServerSignature Off`.

Optional, for SEO: redirect `www` to the main domain with `RewriteCond %{HTTP_HOST} ^www\. [NC]` and `RewriteRule ^ https://eagleglobalhub.com%{REQUEST_URI} [R=301,L]`.

Run `sudo apache2ctl configtest` before `sudo systemctl reload apache2`. Enable `mod_headers` if it is missing. Add `includeSubDomains` to HSTS only once every subdomain serves HTTPS.
