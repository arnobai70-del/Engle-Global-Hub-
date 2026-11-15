# Owner Approval Checklist

Prepared 15 Nov 2026 from the local build, which is uncommitted and not deployed. Nothing below has been approved or published on the owner's behalf.

**Where to review:** sign in as the owner (super admin), then go to Admin › **Content Approval** (`/admin/content-approvals`).

- Each unpublished entry shows its English and Bengali preview, official sources, verification date, what is missing, and its approval state.
- **Approve** needs a ticked confirmation, plus a second confirmation for guides. It marks the entry approved and leaves it as a draft.
- Publishing is a separate step in the editor.
- **Request changes** records your reason in the internal review notes and blocks publishing.
- Entries still marked "Needs source verification" cannot be approved until you confirm the sources in the editor.

## 1. Owner approval checklist

| # | Item | Where | Owner action |
|---|---|---|---|
| 1 | Student's Pass requirements guide | Content Approval | Read EN/BN, confirm the "How we help" text, approve |
| 2 | University admission guide | Content Approval | Read, approve |
| 3 | Tuition fees and costs guide | Content Approval | Read, approve. It gives no fee figures, by design |
| 4 | Pre-departure guide | Content Approval | Confirm the air-ticket service mention, approve |
| 5 | College admission guide | Editor, then Content Approval | Open the two CPE links. If they match the text, set "Needs owner review" and the check date, then approve |
| 6 | Course selection guide | Editor, then Content Approval | Open the CPE EduTrust link, then the same steps as item 5 |
| 7 | Student accommodation guide | Editor, then Content Approval | Confirm the HDB statements on hdb.gov.sg, or delete that section. Confirm "we do not act as a property agent" |
| 8 | Mission, Vision, "What makes us different" (proposed text) | Content Approval | Approve, edit or reject. Until then, the About page keeps its current text |
| 9 | Company Profile | Content Approval | Add the facts in section 3 first, then approve (this also approves the claims) |
| 10 | Business services: export-import, civil construction | Content Approval | Confirm the real scope. Do not mention licences until verified |
| 11 | 4 FAQs (guarantee, tracking, document privacy, payments) | Content Approval | Confirm the payment methods and the privacy wording |
| 12 | 2 inspiration articles (Cox's Bazar, Sylhet) | Content Approval | Add owned or licensed photos, approve |
| 13 | 5 team members (unpublished) | Admin › Team | Each person confirms their name, role and photo |
| 14 | Destinations: Cox's Bazar, Sylhet, Kuakata (unpublished) | Admin › Destinations | Check the text and photos |
| 15 | Eagle Rewards programme (unpublished, no earning rules) | Admin › Rewards | Decide the rules and terms. Get a legal review |
| 16 | 4 forum categories (closed) | Admin › Forums | Assign a moderator before opening any |
| 17 | Bengali text (all) | Admin › Translations and each entry | Native-speaker review (section 5) |
| 18 | "Free consultation" wording on `/study-in-singapore` | Admin › Translations | Confirm consultations are free, or change the wording |

## 2. Pending content summary (local database)

| Type | Count | Review status |
|---|---|---|
| Singapore guides | 4 | Needs owner review |
| Singapore guides | 3 | Needs source verification (approval locked) |
| Page sections (mission, vision, why-different) | 3 | Needs owner review |
| Company profile | 1 | Needs owner review, plus claims approval |
| Business services | 2 | Needs owner review |
| FAQs | 4 | Needs owner review |
| Inspiration articles | 2 | Needs owner review |
| **Published content entries** | **0** | — |

Nothing in the content module is approved or published.

## 3. Company information the owner must provide

None of this has been invented. Each item is either missing or exists only in current settings.

| Item | Current state |
|---|---|
| Company registration (RJSC) number and year established | **Missing** |
| Trade licence number and issuing authority | **Missing** |
| Travel agency registration or licence (e.g. Ministry of Civil Aviation & Tourism / ATAB / IATA, if held) | **Missing**. Do not claim until documented |
| Export/import (IRC/ERC) or construction licences | **Missing**. Do not claim until documented |
| Office address | In settings: Mysha Chowdhury Tower, Ga-30/B, Pragati Sharani, Shahjadpur, Gulshan, Dhaka-1212. **Confirm** |
| Office phone | In settings: 01953626481. **Confirm** it is the official public number |
| Office email | In settings: info@eagleglobalhub.com. **Confirm** the mailbox is monitored |
| Office hours | **Missing** |
| Public contact directory numbers (Admin › Contact Directory) | **0 entries**, so no numbers are shown |
| Team verification (5 drafts: Founder & CEO, Managing Director, Executive Director, Finance Director, Marketing Director / CRM) | Unpublished. Each person must confirm their details and photo |
| Mission and Vision | Proposed drafts only. Settings `company.mission` and `company.vision` are empty |
| Company Profile | Draft. Needs the registration and licence facts above |
| Office photos | **None** in the media library |
| Success stories | **None**. Each needs real evidence and the student's written consent (reference and date) |
| Institution partnerships | **None recorded**. Do not claim any without a signed agreement |

## 4. Source verification results (checked 15 Nov 2026)

| Source | Result |
|---|---|
| ICA: Student's Pass for IHL and PEI students; completion of formalities | Verified (pages last updated 06–09 Nov 2026) |
| ICA: visa-required countries (Bangladesh listed) | Verified (28 Oct 2026) |
| ICA: SG Arrival Card (submit within 3 days including the arrival day; free) | Verified (28 Oct 2026). Pre-departure wording corrected; the unverified MyICA app mention was removed |
| MOE: polytechnic Direct Admissions Exercise | Verified (06 Jul 2026). College guide now says Year 12 international qualifications may apply |
| MOE: Tuition Grant and employment bond (3 years in a Singapore entity) | Verified (23 Jun 2026) |
| MOE: university admissions | Verified (03 Jul 2026). The page only directs applicants to university websites; the university guide wording was corrected to match |
| CPE (cpe.gov.sg): private education and EduTrust | **Not verifiable**: the site could not be reached from the verification environment. Two guides stay locked |
| HDB (hdb.gov.sg): renting rules for students | **Not verifiable**: the rules load dynamically and could not be read. The accommodation guide stays locked |

## 5. Bengali review findings

- **Audited:** 416 items (all `lang/bn.json` phrases plus every Bengali title, summary and body in the CMS).
- **Mechanical issues found:** 0. That covers English full stops after Bengali text, double or trailing spaces, missing `:placeholders` and untranslated strings.
- **Terminology is consistent:** ইমেইল, কনসালটেন্ট, অ্যাপয়েন্টমেন্ট, স্টুডেন্টস পাস, ওয়েবসাইট, অনুসন্ধান, আবেদন.
- **Brand spellings:** "Egabe B2B" stays in Latin letters. "ঈগল গ্লোবাল হাব লিমিটেড" is used for the company name. **Owner: confirm the preferred Bengali spelling.**
- **Status:** every Bengali text is a machine-assisted draft. **None is native-approved.** A native speaker must review it.
- **For the native reviewer** (style choices, not errors):
  - "Document" is translated as কাগজ / কাগজপত্র; ডকুমেন্ট is the alternative.
  - "Organic Search" is translated as সার্চ ইঞ্জিন.
- **Flagged for legal or immigration review** (a qualified person, not only a translator):
  1. All 7 guide bodies (Student's Pass, admission, Tuition Grant bond, arrival card and accommodation rules).
  2. "Student Visa / স্টুডেন্ট ভিসা" menu and service labels. Singapore issues a *Student's Pass*, not a visa. The label matches the existing English menu, so it was left unchanged; owner decision.
  3. Decision disclaimers: "Admission decisions are made by each institution… Student's Pass decisions by ICA" and "Rules and requirements can change…".
  4. The payments FAQ and the payment-method sentence (office, bank, mobile banking).
  5. Rewards terms (not written yet) and the existing Terms & Conditions page.
  6. Job visa and work permit wording.

## 6. Production configuration

READY means done in code or the production template. NOT READY means it is missing. REQUIRES OWNER ACTION means a server, account or credential decision.

| Item | Status | Notes |
|---|---|---|
| HTTPS | REQUIRES OWNER ACTION | TLS certificate on the server; `APP_URL=https://…` |
| Database | REQUIRES OWNER ACTION | MySQL server and credentials. Template is ready (`DB_CONNECTION=mysql`); local uses SQLite |
| Environment file | REQUIRES OWNER ACTION | Create the production `.env` from `.env.production.example`; generate a new `APP_KEY` |
| Debug off | READY (template) | `APP_DEBUG=false`, `APP_ENV=production`, `LOG_LEVEL=info` in the template |
| Secure cookies | READY (template) | `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `HTTP_ONLY`, `SameSite=lax` |
| Email delivery | NOT READY / REQUIRES OWNER ACTION | SMTP or provider account plus a verified sender (SPF/DKIM). Local uses the `log` mailer |
| Queue worker | REQUIRES OWNER ACTION | Code ready (`database` queue). Needs a supervised `queue:work` process (`docs/DEPLOYMENT.md` §7) |
| Scheduler | REQUIRES OWNER ACTION | Schedule defined (token purge, failed-job prune, reset cleanup). Needs the cron entry (`docs/DEPLOYMENT.md` §8) |
| Storage | REQUIRES OWNER ACTION | Run `php artisan storage:link`. Private document and receipt disks are never served (READY in code) |
| Backups | NOT READY | Procedure written (`docs/BACKUP_RESTORE.md`). No automated, encrypted job exists yet |
| Restore test | NOT READY | No restore has been rehearsed |
| API keys (Duffel, Viator, Sherpa, payments, SMS, WhatsApp, maps) | REQUIRES OWNER ACTION | None configured. The site falls back to manual enquiry forms. Payment, SMS and WhatsApp adapters do not exist yet |
| Monitoring / uptime | NOT READY | No uptime or error-alert service is configured. Admin › API & Integrations shows queue health |
| Error logging | READY (template) | Daily log files (`LOG_STACK=daily`). No external error tracker is installed; that would be a dependency decision |

## 7. Production blockers

1. Owner approval of the content in sections 1–3, plus native Bengali and legal review.
2. Company facts in section 3 (registration, licences, contacts and team confirmation).
3. Server: HTTPS, MySQL, production `.env`, queue worker, scheduler cron and `storage:link`.
4. Real email transport.
5. Automated backups and one rehearsed restore.
6. Monitoring and alerting.
7. Owner permission to commit, review and deploy the code. Nothing has been committed or pushed.
