# Production Readiness

Status at 15 Nov 2026 (local build, uncommitted). **Verdict: not ready to deploy.** The code is tested and builds. Server access, MySQL verification, email, backups and owner approvals are still missing (section 7).

Production host: **Contabo Cloud VPS 4** (4 vCPU, 8 GB RAM, 100 GB; Webmin-LAMP image; `vmi3709944.contaboserver.net`, Apache 2.4.52 on Ubuntu). There is no Contabo firewall and no Auto Backup. Snapshot `before-release-2026-11-15` was taken on 15 Nov 2026. It currently runs an **older release of this same Laravel application** with live data. The deployment is an in-place upgrade. Details: `docs/DEPLOYMENT.md` §1.

## 1. Verification results (local)

| Check | Result |
|---|---|
| Automated tests | **873 passed, 0 failed** (6,230 assertions) |
| Authorization / API / SEO groups | 12 / 9 / 4 tests, all pass |
| Documents, receipts, private files, sitemap, search, drafts (filtered) | 123 tests, all pass |
| Production build (`npm run build`) | Passes |
| Migrations | 50 applied, 0 pending (SQLite). **Not yet run on MySQL** |
| Routes | 303 registered |
| Responsive smoke (8 public pages × 6 widths, 320–1440 px) | 48 checks: no horizontal overflow, no unlabelled controls |
| `php artisan app:production-readiness` (local `.env`) | 16 PASS, 6 FAIL, 1 WARN. The FAILs are expected with local settings: `APP_ENV`, `APP_DEBUG`, HTTPS URL, `LOG_LEVEL`, secure cookie, mail. The WARN is the missing storage link |
| `ProductionSeeder` | Tested: keeps live role permissions, country switches, homepage text and settings; idempotent; publishes nothing; creates no test user |
## 2. Security review

| Area | Finding |
|---|---|
| Student documents and payment receipts | Private disks, never served; downloads go through permission and policy checks. **Hardened this round:** the default `local` disk's signed-URL routes (`GET/PUT /storage/{path}`), which could reach the private folders with a valid signature, were disabled (`serve => false`); nothing used them. Regression test added. |
| Role-based access | Staff roles, plus `StudentApplicationPolicy` limiting staff to their assigned files (tested) |
| Agent/B2B approval | Portal only for active agents (tested) |
| File uploads | Content-based type checks, size limits, random names (tested) |
| API tokens | SHA-256 hashed, expiring, rate-limited login, purged daily by the scheduler |
| Login rate limiting | Fortify login throttling (existing) |
| Sessions and CSRF | Laravel CSRF on all web forms. Production template sets `SESSION_ENCRYPT=true` and `SESSION_SECURE_COOKIE=true` |
| Content sanitisation | All CMS text is stored as plain text and escaped on output; markup is limited to `## ` headings and `- ` lists (tested against script injection) |
| Forums | Personal-data masking, spam checks, approval queue, reports (tested) |
| Search, sitemap, robots | Public published content only; admin, portals and notifications are disallowed in robots.txt and absent from the sitemap (tested) |
| Redirects | No open redirects, loops or protected paths (tested) |
| Activity logs | Sensitive keys stripped automatically (tested) |
| Analytics | Meta Pixel only on public layouts; no student data is sent |

No exploitable defect was found this round. The `/storage` change is defence in depth.

## 3. SEO review

- Every public page has a unique title, a description, a canonical URL and Open Graph/Twitter tags.
- **Fixed this round:** the Bengali versions of the Singapore page and of bilingual guides declared the English URL as canonical while also announcing a Bengali `hreflang` alternate, which search engines treat as conflicting. Each language version now has a self-referencing canonical.
- Sitemap: published pages only; drafts, previews, admin and portals are excluded (tested).
- Structured data: Service, Article, Restaurant, TouristDestination, FAQPage and BreadcrumbList, depending on the page.
- No misleading claims in any draft: guides have no guarantees, fees or approval rates (tested for "we guarantee").
- `robots.txt` points to the absolute sitemap and blocks private areas.

## 4. Performance (local measurements; production needs its own measurement)

| Measurement | Before | After |
|---|---|---|
| SQL queries: homepage | 57 | 32 |
| SQL queries: About | 39 | 19 |
| SQL queries: other public pages | 14–26 | 10–18 |
| Images without width/height (every page) | 2 (logos) | 0 |

- **Change 1:** the settings-table existence check is now remembered per application instance, instead of being repeated about 11 times per page. The Settings caching rules are unchanged.
- **Change 2:** SEO overrides are read from one cached settings group instead of two lookups.
- **Change 3:** the logos now carry their intrinsic size (CSS still controls the display size), and the footer logo is lazy-loaded.
- **Not changed (owner decisions):**
  - The Travelpayouts script loads about 10 third-party requests on every page.
  - The homepage loads large Unsplash photos (about 3 MB in total locally).
- Local load times (time to first byte 0.8–3 s) come from `php artisan serve` on Windows with SQLite and no OPcache. Measure on the deployed site with PageSpeed Insights.
- **Not done:** automatic image compression or WebP conversion of uploads. Upload already-optimised images; limits are 2–4 MB per image.

## 5. Integrations

| Integration | Adapter | Connection test | Status |
|---|---|---|---|
| Duffel flights | Yes | Yes (read-only) | Needs `FLIGHT_SEARCH_PROVIDER=duffel`, `DUFFEL_ACCESS_TOKEN`. Never tested live. |
| Duffel Stays (hotels) | Yes | No | Needs `HOTELS_ENABLED`, `HOTEL_PROVIDER`, `DUFFEL_ACCESS_TOKEN` |
| Viator (tours) | Yes | No | Needs `TOURS_ENABLED`, `TOUR_PROVIDER`, `TOUR_API_KEY` |
| Sherpa (visa info) | Yes | No | Needs `VISA_ENABLED`, `VISA_PROVIDER`, `VISA_API_KEY`, `SHERPA_API_BASE_URL` |
| Email | Laravel mail | — | Needs `MAIL_*` |
| Payments | Contract and signed webhook only | — | **No gateway adapter.** Offline recording works. |
| SMS, WhatsApp, Maps | Contracts only | — | **No provider adapters.** |
| Mobile API | `/api/v1` | — | Ready; no app exists |

No provider has been tested against a live account. Without one, manual enquiry forms are used.

## 6. Database

- All migrations are reversible and contain no destructive changes to existing data.
- Unique constraints: guide and content slugs per type, destination slugs, service-destination pairs, payment transaction references, forum report per user, webhook event IDs, API token hashes and others.
- Production must use the live server's MySQL/MariaDB database (engine to confirm), not the local SQLite file. Never copy the local database or `.env` to the server.
- Backup and restore procedure: `docs/BACKUP_RESTORE.md`; rollback: `docs/ROLLBACK_PLAN.md`; migration risk review: `docs/DEPLOYMENT.md` §6. Backups cover the database **and** the private document and receipt folders.

## 7. Remaining blockers before going live

| # | Blocker | Label |
|---|---|---|
| 0 | **Webmin login (port 10000) is public**, there is no Contabo firewall and ufw status is unknown. Restrict Webmin, update it, enable ufw for 22/80/443 (`docs/DEPLOYMENT.md` §1, Webmin and firewall) | REQUIRES OWNER APPROVAL (security, high priority, independent of the release) |
| 1 | Read-only server audit output (`docs/DEPLOYMENT.md` §2): PHP version and extensions, database engine, disk, firewall, layout, existing workers and backups | BLOCKED (needs SSH output from the owner) |
| 2 | Staging run on the server: migrations on the live database engine, `ProductionSeeder`, smoke checks | BLOCKED (needs 1) |
| 3 | Encrypted pre-release backup, Contabo snapshot, off-server target, one rehearsed restore (`docs/BACKUP_RESTORE.md`) | NEEDS CONFIGURATION / REQUIRES OWNER APPROVAL |
| 4 | Outgoing email: provider, `MAIL_*`, SPF/DKIM/DMARC at Namecheap | REQUIRES OWNER APPROVAL |
| 5 | Supervisor queue worker and scheduler cron | REQUIRES OWNER APPROVAL |
| 6 | Apache security headers, `ServerTokens Prod`, optional `www` redirect | REQUIRES OWNER APPROVAL |
| 7 | Monitoring: uptime and backup heartbeat alerts | NEEDS CONFIGURATION |
| 8 | Content approval and company facts (`docs/OWNER_APPROVAL_CHECKLIST.md`). Not a deploy blocker: drafts stay unpublished | REQUIRES OWNER APPROVAL |
| 9 | Commit, review and push the code (nothing committed yet), then the deployment itself | REQUIRES OWNER APPROVAL |
