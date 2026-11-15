# Rollback Plan

**Readiness: prepared, not yet rehearsed.** It must be rehearsed once on staging before go-live.

## Decision rule

Roll back **code** if, after release, any of these happens and cannot be fixed within 15 minutes:

- the homepage or login fails
- existing users cannot sign in
- the error rate rises in `storage/logs`
- `app:production-readiness` shows a FAIL
- private documents become reachable

Roll back the **database** only if data is corrupted. This is not expected, because all 30 new migrations are additive.

## 1. Code rollback (minutes, no data loss)

The previous release works with the new schema: it ignores the new tables, and the new columns are nullable or defaulted. So the database stays as it is.

**Release-folder layout:**

```bash
cd /var/www/eagleglobalhub
PREV=$(ls -1t releases | sed -n 2p)            # previous release folder
ln -sfn releases/$PREV current.new && mv -T current.new current
cd current && php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo systemctl reload apache2                     # clears OPcache (or php8.3-fpm)
php artisan queue:restart
```

**In-place layout:**

```bash
cd /path/to/app && php artisan down
# restore the pre-release code archive (docs/BACKUP_RESTORE.md §2), keeping .env and storage/
age -d -i key.txt code-<stamp>.tar.gz.age | tar -xzf - -C /path/to/app
composer install --no-dev --optimize-autoloader --no-interaction
php artisan optimize:clear && php artisan optimize
sudo systemctl reload apache2 && php artisan queue:restart && php artisan up
```

Notes:
- Queued jobs created by the new release may fail on old code. They go to `failed_jobs` and can be retried after re-release. Do not delete them.
- Records created in new tables during the failed release are kept and become visible again on re-release.

## 2. Database rollback (only for data corruption)

Do **not** run `php artisan migrate:rollback` on production by reflex. It drops the new tables and deletes everything entered in them, for example student applications and documents.

1. Run `php artisan down`.
2. Restore the **pre-release** database dump into a new database (`docs/BACKUP_RESTORE.md` §4) and point `DB_DATABASE` at it.
3. Perform the code rollback (§1).
4. Records created between the backup and the rollback are lost. Export them from the old database first if they matter, for example new users and enquiries.
5. Run `php artisan up`. The owner decides when to retry the release.

If the only issue is the new schema and the new tables are still empty, `php artisan migrate:rollback` (without options) is acceptable instead. It reverts only the last batch, which is this release's migrations. Run `php artisan migrate:status` first to confirm the batch number.

## 3. Full-server fallback

If the server itself is damaged, restore the **Contabo snapshot** taken before the release (customer panel). Everything written after the snapshot is lost, so use this only as a last resort and with owner approval.

## 4. After any rollback

- Record what happened, the time and who decided, in the private release record.
- Keep the failed release folder for diagnosis. Do not delete logs.
- Re-run the smoke checks (`docs/DEPLOYMENT.md` §10).
