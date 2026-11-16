# UI Final Review — Singapore Consultancy Redesign

Review date: 16 Nov 2026. The redesign is local only, on `main`, uncommitted and not deployed.

Preview gallery: `verification/ui-preview/index.html`. It is git-ignored.

## Update — UI completion round (16 Nov 2026)

The owner approved four tasks: complete Bengali, keep the new homepage, add three pages, and fix SEO canonicals. Everything is local and uncommitted; nothing is pushed or deployed.

### Results

| Check | Result |
|---|---|
| Full test suite | **889 passed, 0 failed** (6,345 assertions). Baseline 879; 10 new tests |
| Production build (`npm run build`) | Passes |
| Migrations | 51 applied, 0 pending. New: `add_public_listing_to_institutions_table` (additive: `is_public` default false, `publicly_verified_at` nullable) |
| Routes | 306 (3 new public routes) |
| Responsive check (51 pages: EN + BN public pages, portals, admin) at 320, 375, 390, 768, 1024 and 1440 px | 306 captures. 3 overflows were found at 320 px and fixed; re-check 0 |
| Header fit (EN and BN, 1024–1920 px) | Full menu at ≥1366 px in both languages, ☰ menu below. No overflow at any width (it was overflowing by 26–174 px before; fixed) |
| Gallery | 51 previews × desktop and mobile = 102 screenshots, every one a working route |
| Bengali leftover-English scan (public pages, `?lang=bn`) | 0 untranslated strings on the 7 requested pages. Remaining: the hidden anti-spam field label only |

### 1. Bengali translation

- **Translated:**
  - Visa Services, Work Visa, Hotels & Homes, Tours, Support, Terms & Conditions and the About cards;
  - their demo catalogue text, buttons, form labels, FAQs and legal notes;
  - country names in dropdowns;
  - the footer default text and newsletter heading;
  - validation messages (`lang/bn/validation.php`, `auth.php`, `passwords.php`, plus the custom enquiry messages).
- **Strings added this round:** 377, taking `lang/bn.json` from 462 to 839 (plus the three new validation files).
- **Owner text:** text the owner has edited in admin (travel page settings, site tagline) is shown exactly as entered. Only the built-in defaults follow the language.
- **Header length:** "Apply Now" is now "আবেদন করুন" in Bengali (shorter and more natural).
- **Review status:** machine-assisted translation. **Not native-reviewed and not legally reviewed.**
- **Wording that needs legal or immigration review before relying on it:**
  1. Terms & Conditions: all notices (flight availability, booking confirmation, privacy).
  2. Visa page: the "Important" note, the visa FAQ and "Before you apply".
  3. Work Visa page: "No guarantee", eligibility information, safe application support and the FAQ.
  4. The footer phrase "Your trusted global travel and visa partner" (বিশ্বস্ত). The owner should confirm this claim in both languages.

### 2. Three new pages

| Page | Route | Notes |
|---|---|---|
| Singapore Guides index | `/study-in-singapore/guides` | Search and topic filter (topic from the CMS category, or a sensible default per guide). Lists only published guides whose review status is approved. Locked or draft guides never appear (tested). Shows "Guides are on the way" today, because no guide is published yet. |
| Institutions | `/study-in-singapore/institutions` | Lists only institutions the **owner** marks "Show on the public Institutions page" (new owner-only switch on the admin institution form, which records the verification date). Shows name, country, official website, active courses and open future intakes. Email, phone, registration number and notes stay private. A notice states that listing is not a partnership. Today it shows "Institution details are being verified". |
| Travel Services landing | `/travel-services` | Flights, Hotels & Homes, Tours, Tourist Visa, Work Visa, Business Visa and Business Class. Each is labelled **Live Provider**, **Demo Preview** or **Enquiry only**, using the existing registry and routes. It follows the admin feature switches (hidden services disappear). |

All three use the shared banner, cards and buttons and are linked from the header menus and footer. They are in the sitemap only once they have public content (Travel Services always). They have Bengali text and are mobile-responsive.

### 3. SEO canonical fix

See `docs/SEO_AUDIT.md`. In short:
- Login, Register and Forgot password are `noindex,follow`, with a clean self-canonical and `og:url`.
- Reset password has no canonical, so its token is never repeated.
- Fully translated public pages get a self-referencing canonical per language plus hreflang (en, bn, x-default).
- The sitemap and robots.txt rules are kept.

### 4. Homepage

Kept as approved: navy/white/red, the Singapore hero, Apply Now and Free Consultation, the service cards and the student journey. None of the removed sections were restored. No data was deleted.

### Defects found and fixed this round

1. The header menu overflowed its container at all desktop widths (both languages). It is now compact, and switches to the ☰ menu whenever items don't fit (`public/js/egh-sg-nav.js`).
2. At 320 px, the student application detail overflowed (portal card grid minimum width), and long Bengali buttons widened two pages. Both are fixed.
3. During this round a failed scripted edit blanked `consultancy/singapore.blade.php`. It was restored at once from the last commit plus the saved redesign script and this round's edits, then verified by tests and screenshots. The process now refuses to write a file if the result is unexpectedly small.

## How the review was done

- **Pages audited:** 29 previews (28 unique routes; `/dashboard` is previewed twice, once as student and once as admin), plus 13 public pages in Bengali (`?lang=bn`).
- **Browser:** Microsoft Edge (Chromium), driven through DevTools, at 1440 px and 390 px. That is 84 page loads.
- **Checked on every load:**
  - HTTP status
  - title, meta description, canonical, robots and the number of `<h1>` headings
  - images that failed to load
  - failed or 4xx/5xx network requests
  - JavaScript console errors
  - horizontal overflow
  - forms and their CSRF token
- **Links:** every same-origin link on every page was requested with the page's own session: 193 unique URLs.
- **Live form submissions** against the running app: the consultation enquiry, a tourist visa enquiry and the newsletter. A missing required field was also tested.
- **Live CMS edit:** the owner changed the homepage headline in Admin › Translations, saw it on the homepage, then reset it.
- **Temporary data:** QA accounts and test submissions used `@egh-qa.test` addresses and were deleted afterwards. 0 QA records remain.
- **Automated tests:** the full suite was run after the fixes. See the final section.

## Summary

| Check | Result |
|---|---|
| Pages returning 200 | 84 / 84 |
| Internal links that did not return 200 or a redirect | **0 of 193** |
| Broken images | **0** (238 images loaded) |
| Horizontal overflow (desktop or mobile) | **0** |
| JavaScript errors from the site's own code | **0** |
| Third-party errors | Travelpayouts tag only (see "Known issues") |
| Pages with more or fewer than one `<h1>` | 1 found (student dashboard). **Fixed** |
| Forms tested live | 3 of 3 work; validation errors display correctly |
| CMS edit tested live | Works |
| Genuine defects fixed | 5 (see below) |

## Defects found and fixed

1. **The student dashboard had two `<h1>` headings.** The old "Hello" greeting was hidden with CSS but still in the page.
   - Inside the portal, only the greeting is now skipped (`dashboard/customer.blade.php`, `dashboard.blade.php`). Its action buttons stay: Search Flights, My Account, and the Agent Portal link for agents.
   - The CSS workaround was removed. A test was added.
2. **Page titles were escaped twice.** This predates the redesign. Browser tabs and search results showed "Tours &amp;amp; Visa" and "Hotels &amp;amp; Stays". The section values are now decoded once before Blade escapes them (`layouts/site.blade.php`). Output stays escaped, so it is still XSS-safe. A test was added.
3. **The social-sharing tags were also escaped twice.** This predates the redesign.
   - The `og:image` URL was broken (`&amp;amp;`), so Facebook and WhatsApp previews would lose their image.
   - `og:title`, `og:description` and the Twitter tags were affected on Contact, Terms and Business Services.

   All are fixed and covered by the same test.
4. **Admin › Homepage switches described the old design.** "Promotion banners", "Destination slider" and similar labels no longer matched anything on the homepage, and several switches controlled nothing.
   - The switches are relabelled to the sections they now control: Offers card, Our Complete Services, How it works, Visa & Travel Services, Explore Bangladesh and Newsletter.
   - A note explains that the hero text is edited in Admin › Translations.
   - The old hero fields are marked as legacy.

5. **The Explore Bangladesh meta description named three places that are still unpublished drafts** (Cox's Bazar, Sylhet, Kuakata). An existing test forbids showing unpublished destinations, and it only passed because of the double-escaping bug in item 2. The description is now generic until destinations are published.

## Page-by-page results

Legend: ✅ pass · ⚠️ pass with a note.

### Public website (guest)

| # | Page | Route | Desktop / Mobile | Links | Forms / backend | SEO | Bengali | Notes |
|---|---|---|---|---|---|---|---|---|
| 1 | Homepage | `home` | ✅ / ✅ | 28 OK | Newsletter: live submit OK | ✅ unique title, 1 h1, canonical | ⚠️ newsletter heading and footer text come from settings (English) | Guides and Explore Bangladesh sections are hidden until content is published |
| 2 | Study in Singapore | `consultancy.singapore` | ✅ / ✅ | 27 OK | Consultation enquiry: live submit OK, reference shown | ✅ self-canonical per language, hreflang | ✅ (country names stay English) | Guides list shows "being reviewed" until a guide is published |
| 3 | Visa Services | `visa.index` | ✅ / ✅ | 27 OK | Demo search form (GET) | ✅ | ⚠️ page body is English only (pre-existing) | "Demo Preview" notice shown |
| 4 | Work Visa | `work-visa.index` | ✅ / ✅ | 28 OK | Links to work-visa enquiry | ✅ | ⚠️ body English only (pre-existing) | |
| 5 | Hotels | `hotels.index` | ✅ / ✅ | 27 OK | Demo search (GET) | ✅ | ⚠️ body English only (pre-existing) | Sample stays are labelled "Demo" |
| 6 | Tours | `tours.index` | ✅ / ✅ | 27 OK | Demo search (GET) | ✅ | ⚠️ body English only (pre-existing) | |
| 7 | Explore Bangladesh | `explore-bangladesh.index` | ✅ / ✅ | 27 OK | — | ✅ | ✅ | No destination is published yet, so it shows a "being prepared" message |
| 8 | About Us | `about` | ✅ / ✅ | 27 OK | — | ✅ | ⚠️ "What we support" cards are English only (pre-existing) | Mission and Vision drafts are still waiting for owner approval |
| 9 | Contact / Support | `support` | ✅ / ✅ | 27 OK | — | ✅ (og fixed) | ⚠️ body English only (pre-existing) | |
| 10 | Offers | `offers.index` | ✅ / ✅ | 27 OK | — | ✅ | ✅ | No offers published |
| 11 | Get Inspired | `inspiration.index` | ✅ / ✅ | 27 OK | — | ✅ | ⚠️ banner text English only (pre-existing) | Nothing published yet |
| 12 | Tourist Visa enquiry | `service-enquiries.create` | ✅ / ✅ | 36 OK | Live submit OK; a missing country shows a clear error | ✅ | ✅ | |
| 13 | Terms & Conditions | `terms` | ✅ / ✅ | 27 OK | — | ✅ (og fixed) | ⚠️ legal text English only (pre-existing; needs a legal translation) | |
| 14 | Login | `login` | ✅ / ✅ | 3 OK | Login works (used by every portal check) | ⚠️ no canonical tag (pre-existing, auth layout) | ✅ | |
| 15 | Register | `register` | ✅ / ✅ | 2 OK | Form renders with CSRF | ⚠️ no canonical tag (pre-existing) | ✅ | Not submitted, to avoid creating a real account |

### Singapore Guides

| # | Page | Route | Result | Notes |
|---|---|---|---|---|
| 16 | Student's Pass guide (owner preview) | `admin.content.preview` | ✅ / ✅, 34 links OK | All 7 guides are unpublished drafts. The public URL (`consultancy.guides.show`) returns 404 until the owner approves and publishes. That is intended. |

### Student Portal (customer account with one application)

| # | Page | Route | Result | Notes |
|---|---|---|---|---|
| 17 | Dashboard | `dashboard` | ✅ / ✅, 34 links OK | Welcome, counts and progress bar come from real data. The duplicate h1 is fixed. |
| 18 | My Applications | `student.applications.index` | ✅ / ✅ | |
| 19 | Application detail | `student.applications.show` | ✅ / ✅ | Document upload, appointment request and message forms are present with CSRF. Their behaviour is covered by existing tests. |
| 20 | Notifications | `notifications.index` | ✅ / ✅ | Staff still see this page inside the admin layout (unchanged). |
| 21 | My Account | `account.overview` | ✅ / ✅ | Profile update form present. |

### Tourist Portal

| # | Page | Route | Result | Notes |
|---|---|---|---|---|
| 22 | My Enquiries | `service-enquiries.mine` | ✅ / ✅ | The Tourist Portal is the same customer account as the Student Portal. Only the sidebar title differs. |

### Agent Portal (active agent)

| # | Page | Route | Result | Notes |
|---|---|---|---|---|
| 23 | Dashboard | `agent.index` | ✅ / ✅, 36 links OK | |
| 24 | Refer a student | `agent.referrals.create` | ✅ / ✅ | Form with CSRF. Behaviour is covered by `AgentPortalTest`. |

### Admin Panel (owner / super-admin)

| # | Page | Route | Result | Notes |
|---|---|---|---|---|
| 25 | Dashboard | `dashboard` | ✅ / ✅, 42 links OK | Figures are real counts, and each staff role only sees the figures its permissions allow (tested). |
| 26 | Content Approval | `admin.content.approvals` | ✅ / ✅, 83 links OK | Approve and reject forms per entry. Guides locked for source checks show no Approve button (intended). |
| 27 | Website Content | `admin.content.index` | ✅ / ✅ | |
| 28 | Student Applications | `admin.student-applications.index` | ✅ / ✅ | |
| 29 | Settings | `admin.settings.index` | ✅ / ✅ | Redirects to `/admin/settings/manage` (pre-existing behaviour). |

Admin pages have no meta description. That is acceptable: they are private and not indexed.

## CMS content editing

- **Live:** the owner changed the homepage headline in Admin › Translations (English). It appeared on the homepage, and clearing the field restored the original. The same applies to Bengali.
- **Note:** the header tagline uses the same phrase ("Your Gateway to Study in Singapore"), so editing that phrase changes both places.
- **Covered by automated tests:** content entries (create, edit, revisions, approval gate, publish), page sections (mission, vision, why-different), FAQs, homepage settings and switches, media, team, destinations, offers and translations.

## SEO and Bengali

- **SEO:**
  - Every public page has a unique title, a description, a self-referencing canonical, `index,follow`, one `<h1>` and `<html lang>`.
  - The Study in Singapore page has `hreflang` en/bn plus a Bengali self-canonical.
  - The sitemap and robots rules are unchanged and tested.
- **Bengali:** all 96 strings added by the redesign are translated, and the translation coverage test passes. The remaining English on `?lang=bn` pages is **not caused by the redesign**:
  - **Content from settings and the database** (footer description, newsletter heading, country names). To be translated by the owner in Admin, or left as data.
  - **Page bodies written in English before this work:** Visa, Work Visa, Hotels, Tours, Support, Terms, About cards and the Get Inspired banner. Translating them needs a native speaker, and Terms and visa wording need a legal review.

## Old homepage features that were removed

These belonged to the previous "flight-first" homepage and are not part of the reference design. Their data and admin screens remain and nothing was deleted, but they are no longer shown on the homepage:

| Old feature | Where it lives now |
|---|---|
| Flight search box on the homepage | Flights page (`/flights`) for users with flight permission; Flight Services tile |
| Assurance strip (5 statements) | Removed from the homepage |
| Promotion banners (flight / hotel / work visa) | Offers card → `/offers` |
| 10 travel service tiles with status | 7 reference tiles. Status badges now sit on the Visa & Travel cards |
| Popular destinations slider (Dubai, Bali …) | Replaced by Explore Bangladesh (published destinations only) |
| Service panels (Visa / Work Visa / Holiday) | Visa & Travel Services cards |
| "Why choose" benefits | "How it works" steps |
| Mobile app section | Removed (no app is published) |
| Hero title, subtitle and image from Admin › Homepage | Hero text via Admin › Translations. The old fields are marked "legacy" |

These remain: news ticker, newsletter, SEO settings, structured data (TravelAgency) and the section switches. The old `egh-home.css` and `egh-home.js` files remain on disk but are no longer loaded by the homepage.

## Pages in the reference that do not exist as separate pages

The reference image lists pages the application does not have as their own routes:

| Reference page | Current equivalent |
|---|---|
| Singapore Guides (index page) | Guides section on Study in Singapore (`#guides`) |
| Institutions / Courses & Intakes / Admission Information | Tiles on Study in Singapore that link to the matching guide once published |
| Travel Services (landing page) | Travel Services menu: Flights, Hotels, Tours, Offers, Rewards |
| Group Tours | Tours page; tour enquiry form |
| Contact Us (separate from Support) | Support page |
| Separate Tourist Portal | Same customer account; sidebar titled "Tourist Portal" on enquiry pages |

Creating these would be new features, which is out of scope for this review.

## Existing pages not in the 29-page gallery

These routes use the new layout but were not part of the preview set:
- Business Visa and other enquiry forms (same template as the Tourist Visa enquiry)
- flight pages (signed-in, flight permission)
- Company Profile, Business Services, Success Stories, Restaurants, Reviews, Rewards, Forums, Search
- destination and content detail pages
- B2B registration
- bookings
- the remaining admin screens (styled by the shared admin stylesheet)

They load without errors; this was checked by the link check.

## Preview screenshots versus real browser rendering

- The screenshots were taken in a real browser (Edge/Chromium) at the stated widths, from the running Laravel app. They are not mock-ups.
- **The full-page capture differs from scrolling in these ways:**
  - The sticky header appears only at the top.
  - Lazy-loaded images were forced to load, so all photos appear. In a real browser they load as the visitor scrolls.
- **Local-only elements:**
  - The yellow "Development / sandbox environment" bar does not appear in production.
  - The Travelpayouts widget is blocked on localhost (CORS), so it is absent from screenshots but will load on eagleglobalhub.com.
- **Fonts:** Poppins (headings) and Instrument Sans (body) are bundled with the build and load in both.
- **Photos** are Unsplash links (as before) and depend on the visitor's connection to images.unsplash.com.

## Known issues not changed (owner decisions or out of scope)

1. Bengali gaps on older page bodies (see "SEO and Bengali").
2. Login and Register have no canonical tag. They could be set to `noindex`.
3. The Travelpayouts tag logs a CORS error on localhost. It is expected to work on the live domain; confirm after deployment.
4. Editing the headline phrase also changes the header tagline (shared phrase).
5. Guides, destinations, offers and team stay hidden until the owner publishes them (by design).

## Final verification

- **Full test suite:** 879 passed, 0 failed (6,225 assertions), after all fixes.
- **Production build** (`npm run build`): passes.
- **Branch:** `main`. Nothing committed, pushed or deployed.
