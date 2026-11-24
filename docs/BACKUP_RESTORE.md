# Backup and Restore

Applies to the Contabo server (see `docs/DEPLOYMENT.md` §1). Paths assume the release layout `/var/www/eagleglobalhub/shared/`; for the current single-folder layout, use `<app>/storage` and `<app>/.env` instead.

**Status: NOT READY.**
- No automated backup exists yet, and no restore has been rehearsed.
- Contabo **Auto Backup is not subscribed** (owner, 15 Nov 2026).
- A manual Contabo snapshot **`before-release-2026-11-15`** was taken on 15 Nov 2026. Check its expiry date in the Contabo panel; snapshots are not kept indefinitely.
- A snapshot is a whole-server restore point stored with the same provider. It is **not** an off-server, encrypted backup and does not replace §3.

## 1. What to back up

| Data | Location | Sensitivity |
|---|---|---|
| Database | MySQL/MariaDB (engine to confirm) | Personal data: students, users, payments |
| Student documents | `storage/app/private/student-documents` | **Highly sensitive** (passports, transcripts) |
| Payment receipts | `storage/app/private/payment-receipts` | Sensitive |
| Legal documents (original and redacted review copies) | `storage/app/private/legal-documents` | **Highly sensitive** (directors' NIDs, TINs, addresses, signatures) |
| Public uploads | `storage/app/public`: media library, team photos, destination images, content covers | Public |
| `.env` | `shared/.env` | **Secret**: contains APP_KEY and passwords. Store it separately (password manager or secret store), never next to the data backups |

Without the original **APP_KEY**, encrypted sessions and any encrypted columns cannot be read. Keep a copy of it in the owner's password manager.

## 2. Pre-release backup (mandatory before every deployment)

The owner approves installing `age` (`sudo apt install age`), a small encryption tool. GnuPG works too.

```bash
set -euo pipefail
umask 077
STAMP=$(date +%F-%H%M); OUT=/var/backups/egh; mkdir -p $OUT
RECIPIENT="age1..."   # owner's public age key; the private key never goes on the server

# Database: consistent snapshot without locking InnoDB tables.
# Credentials come from ~/.my.cnf (mode 600), never on the command line.
mysqldump --single-transaction --quick --routines --triggers --no-tablespaces <db_name> \
  | gzip | age -r "$RECIPIENT" > $OUT/db-$STAMP.sql.gz.age

# Private and public files
tar -C /var/www/eagleglobalhub/shared -czf - storage/app \
  | age -r "$RECIPIENT" > $OUT/files-$STAMP.tar.gz.age

# Current code (for the in-place layout), so the old release can be restored
tar -C /path/to/app --exclude=storage --exclude=.env -czf - . \
  | age -r "$RECIPIENT" > $OUT/code-$STAMP.tar.gz.age

# Verify the archives decrypt and are complete (run on the owner's machine with the private key):
#   age -d -i key.txt db-$STAMP.sql.gz.age | gunzip | tail -1   -> "-- Dump completed on ..."
#   age -d -i key.txt files-$STAMP.tar.gz.age | tar -tzf - | wc -l
sha256sum $OUT/*-$STAMP.* > $OUT/SHA256SUMS-$STAMP
```

Also take a **Contabo snapshot** of the VPS from the customer panel just before the release, if the plan includes snapshots.

## 3. Automated daily backups

Cron for the backup user, for example at 02:30 server time:

```cron
30 2 * * * /usr/local/bin/egh-backup.sh >> /var/log/egh-backup.log 2>&1
```

`egh-backup.sh` runs the database and files steps from §2, then copies the encrypted files **off-server** and applies retention.

| Topic | Recommendation |
|---|---|
| Off-server target | Object storage (Contabo Object Storage, Backblaze B2, AWS S3) using `rclone` with a write-only key, **or** an SFTP account on another machine. Choosing and paying for the target is an owner decision. |
| Retention | 14 daily, 8 weekly and 12 monthly copies. Delete older copies on the target with a lifecycle rule. |
| Encryption | Done on the server before upload. The storage provider never sees plaintext. |
| Alerting | If the script fails, the log shows it. Add an uptime or heartbeat check (e.g. healthchecks.io) that alerts when no successful run arrives in 26 hours. |

## 4. Restore procedure

Always rehearse on **staging** first (`docs/DEPLOYMENT.md` §3). Never restore over production without owner approval.

1. Put the target app in maintenance mode: `php artisan down`.
2. Decrypt the backup on a trusted machine, or on the server using a temporary key that is deleted afterwards.
3. Restore the database into an **empty** database:
   ```bash
   mysql -e "CREATE DATABASE egh_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   gunzip < db-<stamp>.sql.gz | mysql egh_restore
   ```
   Switch production to `egh_restore` by changing `DB_DATABASE` (then run `php artisan config:cache`) **only after** step 6 passes. Keep the old database until the owner confirms.
4. Restore the files into `shared/storage/app`:
   ```bash
   tar -xzf files-<stamp>.tar.gz -C /var/www/eagleglobalhub/shared
   sudo chown -R www-data:www-data /var/www/eagleglobalhub/shared/storage
   find /var/www/eagleglobalhub/shared/storage -type d -exec chmod 750 {} \;
   find /var/www/eagleglobalhub/shared/storage -type f -exec chmod 640 {} \;
   ```
5. Run `php artisan migrate --force`, in case the backup predates the release, then `php artisan storage:link`.
6. Verify:
   - `php artisan app:production-readiness` passes.
   - The row counts of `users`, `students`, `student_applications`, `student_documents` and `payment_records` match the counts recorded at backup time.
   - A student can download their own document, and another user gets 403 or 404.
   - The homepage and admin load.
7. Run `php artisan up`.

## 5. Restore verification record

After every rehearsal, write down:
- the date
- the backup used
- the time taken
- the row counts
- the result
- who ran it

**A backup that has never been restored is not a verified backup.** Rehearse once before go-live and then quarterly.

## 6. Storage permissions

- The private folders `storage/app/private/*` are never inside `public/`, and the private disks are not served (`serve => false`). Only `storage/app/public` is linked at `public/storage`.
- `.env` should be mode `640` (owner deploy user, group `www-data`). `storage` and `bootstrap/cache` must be writable by `www-data`; everything else should be read-only to it.
- Check: `curl -I https://eagleglobalhub.com/.env` and `curl -I https://eagleglobalhub.com/storage/student-documents/` must return 403 or 404. Both returned 404 or 403 on the live site on 15 Nov 2026.
