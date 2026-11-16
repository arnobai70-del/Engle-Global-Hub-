# Complete Feature Audit

Date: 16 Nov 2026. This covers the local build only: uncommitted, not pushed, not deployed, and no server changes.

**Sources:**
- *Travel & Visa Agency Business Requirements.pdf*
- the previous website (git history and the live site at eagleglobalhub.com, read-only)
- the current codebase and its routes, views, controllers and seeders

**Status legend (provider states on the website):**

| Label | Meaning |
|---|---|
| **LIVE PROVIDER** | Shown only after an admin connection test passed for the current configuration. Changing the key or provider resets it. |
| **WAITING FOR API** | The feature needs an external provider that is not connected or not yet tested. An enquiry fallback is offered where one exists. |
| **ENQUIRY ONLY** | A person on our team handles the request. No API is involved. |
| **DEMO PREVIEW** | Local development data only (for example the flight fixture). It is never used in production. |
| **DISABLED** | Switched off in Admin › Feature Control. |

Today **nothing is LIVE PROVIDER**. No provider API has been connected and tested, and the production template sets every provider to `unavailable`.

"Test result" names the automated tests that cover the item. Every one of them passes (see the final section).

## 0. Logo

| Check | Result |
|---|---|
| Original asset | `public/images/eagle-global-hub-logo.png` (240×241 PNG, "EGH / EAGLE GLOBAL HUB LTD."), committed 28 Sep 2026 |
| Same as the live website | Yes: the file served by eagleglobalhub.com has the identical SHA-256 (`a5ac288e…`) |
| Placement | Header, footer, login/register (auth layout), admin sidebar, error pages, portal sidebar |
| Aspect ratio | Width set with `height: auto` and the intrinsic `width="240" height="241"` attributes. Never stretched or cropped |
| Guard | `CompleteFeaturePagesTest` fails if the file is replaced or the header drops it |

## A. Core services

| PDF requirement | Old website | Dedicated page | Actions | API dependency → status today | Admin control | Tests |
|---|---|---|---|---|---|---|
| Student Visa / Singapore consultancy | — | `/study-in-singapore` (consultancy), `/services/student-visa` (all 7 student destinations), `/study-in-singapore/guides`, `/study-in-singapore/institutions` | Free Consultation form (stores a Student Enquiry); per-country link pre-selects the destination | None → ENQUIRY ONLY | Student Enquiries, Applications, Courses, Institutions, Content Approval, Services & Destinations | `Consultancy/*`, `CompleteFeaturePagesTest`, `NewPublicPagesTest` |
| Tourist Visa | Nav "Visa" | `/services/tourist-visa` (21 PDF countries) | Per-country enquiry (pre-selected); enquiry form | Visa info provider → WAITING FOR API; service → ENQUIRY ONLY | Service Enquiries, Services & Destinations | `ServiceEnquiryTest`, `CompleteFeaturePagesTest` |
| Business Visa | — | `/services/business-visa` | Enquiry form | as above | as above | as above |
| Job / Work Visa | "Work Visa (New)" | `/work-visa` (now lists the 7 PDF work-permit countries, each linking to a pre-selected enquiry) | Work visa enquiry | None → ENQUIRY ONLY | Service Enquiries, Services & Destinations | `WorkVisaPagesTest` |
| Air Ticket | Flight search on the homepage | `/services/air-ticket`; `/flights` for signed-in customers | Flight enquiry (sign-in required); search only when the provider is live | Flight provider → WAITING FOR API (local: DEMO PREVIEW fixture); payments → WAITING FOR API | Flight Enquiries, API & Integrations (Duffel connection test) | `Flight/*`, `CompleteFeaturePagesTest` |
| Hotel Booking | Nav "Hotels" | `/hotels` | Hotel enquiry; search disabled until the provider is live | Hotel provider → WAITING FOR API | Service Enquiries, Feature Control | `HotelFoundationTest` |
| Tour Packaging | Nav "Tours" | `/tours` | Tour enquiry; search disabled until the provider is live | Tour provider → WAITING FOR API | Service Enquiries | `TourFoundationTest` |
| Trains | — | `/services/trains` | Train enquiry | Transport booking API → WAITING FOR API | Service Enquiries | `CompleteFeaturePagesTest` |
| Bus | — | `/services/bus` | Bus enquiry | as above | as above | as above |
| Cars | — | `/services/cars` | Car enquiry | as above | as above | as above |
| Bike | — | `/services/bike` | Bike enquiry | as above | as above | as above |
| Business Class | — | `/services/business-class` | Quote request → flight enquiry with business cabin | Flight provider → WAITING FOR API | Flight Enquiries | `BusinessModulesTest`, `CompleteFeaturePagesTest` |
| Holiday Packages | Homepage panel "Holiday Packages" | `/services/holiday-packages` | Tour enquiry, pre-set to "Holiday package" | Tour provider → WAITING FOR API; service → ENQUIRY ONLY | Service Enquiries, Offers | `CompleteFeaturePagesTest` |
| Group Tours | — | `/services/group-tours` | Tour enquiry, pre-set to "Group tour" | as above | as above | as above |
| Private Tours | — | `/services/private-tours` | Tour enquiry, pre-set to "Private tour" | as above | as above | as above |

The Travel Services hub at `/travel-services` links all of these, grouped into flights/hotels/tours, visas and transport, each with its status badge.

## B. Country catalogues (unchanged, admin-managed)

Kept exactly as in the PDF and managed in **Admin › Services & Destinations**. Nothing was removed or replaced. Dubai is stored as "United Arab Emirates" and **shown as "Dubai (United Arab Emirates)"**.

| Catalogue | Countries | Shown on |
|---|---|---|
| Tourist Visa (21) | India, Sri Lanka, Thailand, Malaysia, Singapore, China, Philippines, Egypt, Dubai, Vietnam, South Korea, Pakistan, Indonesia, Cambodia, Canada, Germany, Sweden, UK, USA, France, Austria | `/services/tourist-visa`, `/services/business-visa`, tourist visa enquiry form |
| Student Visa (6 + Singapore) | Singapore (primary), Malaysia, Bulgaria, UK, Japan, South Korea, Cyprus | `/services/student-visa`, consultation form |
| Work Permit (7) | Dubai, Malaysia, Japan, Jordan, Saudi Arabia, Canada, Belarus | `/work-visa`, work visa enquiry form |

Test: `CompleteFeaturePagesTest::test_service_pages_keep_the_pdf_country_catalogues_and_preselect_the_enquiry`, `Admin/ServiceDestinationManagementTest`.

## C. Company and business modules

| PDF requirement | Page | Status | Admin control | Notes |
|---|---|---|---|---|
| Affiliator Contact Directory | `/support#directory` | Waiting for owner data | Contact Directory | Shows an honest "being confirmed" message until numbers are added |
| 10 Phone Numbers | `/support#directory` | as above | Contact Directory (maximum 10 enforced) | `BusinessModulesTest` |
| One 24/7 IP Calling Number | `/support#directory` | as above | Contact Directory (one IP line enforced) | as above |
| Company Profiles | `/company` | Draft (owner approval) | Website Content › Company Profile, Content Approval | Hidden until approved and published |
| Success History | `/success-stories` | No stories yet | Website Content › Success Story (consent required) | |
| Pending Files | Internal: Admin › Student Applications (status filter) | Working | Student Applications | Staff only, by design |
| Upcoming Success | Internal: Admin › Upcoming Success | Working | Upcoming Success | Staff only, by design |
| Mission & Vision | `/about` | Proposed drafts | Website Content › Page Section, Content Approval | |
| Export & Import | `/services/export-import`; `/business-services/export-import` once published | Enquiry working | Service Enquiries, Website Content | |
| Civil & Construction Work | `/services/civil-construction`; projects once published | Enquiry working | as above | |
| Why Choose Us? | Homepage "How it works" + `/about` | Working | Homepage switches, Page Section | |
| কেন আমরা ব্যতিক্রম? | `/about` (Bengali) | Proposed draft | Page Section `why-different` | |
| Our Employees | `/about#team` | 5 profiles waiting for verification (empty state shown) | Team | |
| Our Office Environments | `/about#office` | No photos yet (empty state shown) | Media Library › Office gallery | |
| Egabe B2B | `/b2b/register`, `/agent` | Working | Agents | `BusinessModulesTest`, `AgentPortalTest` |
| Foreigner Support | `/services/foreigner-support` | Enquiry working | Service Enquiries | |

## D. Travel and discovery modules

| PDF requirement | Page | Status | Admin control | Notes |
|---|---|---|---|---|
| Restaurants | `/restaurants` | No listings yet | Website Content › Restaurant | |
| Discover | `/explore-bangladesh`, `/get-inspired` | Drafts | Destinations, Website Content | |
| Get Inspired for Your Next Trip | `/get-inspired` | Two article drafts | Website Content › Get Inspired | |
| Places You May Like | Destination and content detail pages | Needs published destinations | Destination recommendations | |
| Explore Bangladesh / Tourist Places | `/explore-bangladesh` | Drafts | Destinations | |
| Cox's Bazar, Sylhet, Kuakata | `/explore-bangladesh/{slug}` | **Drafts, so 404 until published** (by design; no automatic publishing) | Destinations | Owner action |
| Map | `/map` (new) | Interactive map → WAITING FOR API; OpenStreetMap links per place work | Coordinates on content entries | |
| Promotions / Deals / Special Offers | `/offers` (one page; not duplicated) | No current offers | Offers | |
| Reviews | `/reviews` | Working (moderated) | Reviews | |
| Forums | `/forums` | Categories closed | Forums | |
| Rewards | `/rewards` | Programme unpublished | Rewards | |

## E. System and customer modules

| PDF requirement | Page | Status | Notes |
|---|---|---|---|
| Student Application Form | `/study-in-singapore#consultation` | Working | Stores a Student Enquiry; staff open the application |
| Tourist Application Form | `/enquiries/tourist-visa` (and every service enquiry) | Working | Validation in English and Bengali |
| Agent Form | `/b2b/register`, `/agent/referrals/create`, `/agent/service-referrals/create` | Working | |
| Agent Profile | `/agent` (Agent Portal) | Working | Sidebar portal |
| Student / Agent / Tourist Login, Sign In | `/login` | Working | One secure login. The page explains where students, agents and travellers land. No duplicate authentication |
| Support Center | `/support` | Working | |
| Search All | `/search` | Working | Published content only |
| English / Bengali | Every public page | Working | 1,083 strings. Native and legal review pending |
| Mobile APP API | `/api/v1` (`docs/API.md`), `/mobile-app` (new) | API ready; app → WAITING FOR API; push → WAITING FOR API | Store buttons are disabled with an explanation until store links are set |

## F. Previous website features (git history and live site)

| Old feature | Where it is now |
|---|---|
| Homepage flight search box | Air Ticket page and `/flights` (when the provider is live). Removed from the homepage by the owner's design approval |
| Promotion banners ("FLAT 20% OFF…", "Up to 40% OFF…") | **Not restored:** they were unverified sample claims. Real promotions go in Admin › Offers → `/offers` |
| 10 service tiles with status | 7 homepage tiles + `/travel-services` (17 services with status badges) |
| Popular destinations slider (Dubai, Bali, Bangkok, Singapore samples) | **Not restored:** sample content. Explore Bangladesh shows published destinations |
| Service panels (Visa / Work Visa / Holiday) | Visa & Travel Services section; dedicated pages |
| "Why Choose" benefits | "How it works" and About |
| Mobile app section | `/mobile-app` (new dedicated page) |
| Newsletter, news ticker | Kept on the homepage |
| Nav: Flights, Hotels, Tours, Visa, Work Visa, More… (all old links) | All reachable from the new menus and footer |

No data was deleted. Old homepage blocks and settings remain in the database and in Admin › Homepage, which marks them as legacy.

## G. Admin control

| Area | Where |
|---|---|
| Pages and sections, SEO per entry, approval | Website Content, Content Approval, SEO & Redirects |
| Services and their visibility | Feature Control (show/hide per audience), Services & Destinations |
| Countries, courses, institutions (incl. public listing), offers, team, contact directory | Master Data, Courses & Intakes, Institutions, Offers, Team, Contact Directory |
| Every visible label, button text, warning and notice | Translations (English and Bengali overrides) |
| Images, logo-placed media, office gallery | Media Library |
| Footer links, contact details, social links, homepage switches | Settings, Homepage |
| Integrations and public API status | API & Integrations (new "Public status on the website" table; connection test makes a provider Live) |
| Header and footer menus, dropdowns, homepage service tiles, header button, header tagline | **Menus & Branding** (new, permission `menus.manage`, super-admin and admin). See section J |
| Logo | Admin › Homepage upload. The original logo stays the default and is used whenever no uploaded file exists; it is never replaced automatically |

## H. Warnings, errors and notifications

| Item | Implementation |
|---|---|
| Required, invalid and custom field messages | Laravel validation, now also in Bengali (`lang/bn/validation.php`) |
| Waiting for API | `<x-site.api-notice>` on every API-dependent page and badge on every card; redirect notice if a search is attempted |
| Duplicate submissions | Server throttles on public forms, plus a new client guard that disables submit and shows "Sending…" after the first click |
| Permission, expired form, too many attempts, server error, maintenance | New branded pages: 403, 404, 419, 429, 500, 503 (bilingual, `noindex`). 500 and 503 render without the database |
| Login errors | Bengali `auth.php` and `passwords.php` |
| Empty states | Guides, institutions, map, team, office, directory, offers, enquiries, applications |
| Payment and booking status | Existing portal pages (unchanged) |

## I. Verification (16 Nov 2026, local)

| Check | Result |
|---|---|
| Full test suite | **896 passed, 0 failed** (6,572 assertions). 7 new feature tests; 13 tests rewritten from "sample results" to the "Waiting for API" rule |
| Production build | Passes |
| Migrations | 51 applied, 0 pending (none added this round) |
| Routes | 311 (new: `/services/{slug}`, `/services`, `/map`, `/mobile-app`, plus the 404 fallback) |
| Browser crawl (Edge), every same-origin link followed per role | guest 113 pages, student 123, agent 125, owner 292 (653 pages): **0 broken links**, 0 non-200 pages, 0 empty or `#` links, 0 buttons without an action, every page has one `<h1>` |
| Forms flagged by the crawler | Admin filter forms (`GET`, submit to the same page by design) and 6 admin modal forms submitted by JavaScript. All handlers verified |
| Responsive (90 public pages, English and Bengali) at 320, 375, 390, 768, 1024 and 1440 px, plus portals and admin at 390 and 1440 | 0 horizontal overflow; 0 unlabelled form fields |
| Logo | Original file unchanged (hash checked by a test) |
| Preview gallery | `verification/ui-preview/index.html`: 112 previews × desktop and mobile = 224 screenshots, including waiting-for-API states and 404/403 error states |

### Defects found and fixed this round
1. The homepage labelled flights **"Live Provider"** while no flight API was connected (the old registry treated "flights available" as live). It now shows WAITING FOR API.
2. Hotel, tour and visa pages showed **sample results with fake prices and ratings** when no provider was connected. They now show WAITING FOR API with an enquiry button, and searches never return sample data.
3. The Work Visa page listed six **sample destinations** that did not match the PDF. It now shows the 7 PDF work-permit countries.
4. Enquiry forms could not pre-select a country (the country code was not loaded).
5. Unknown URLs ignored the visitor's language; the 404 page now follows it.
6. The Work Visa page failed without a database connection; it is now fail-safe.
7. The Air Ticket waiting notice said "no online alternative" although a flight enquiry exists. It now offers that enquiry.

## J. Admin control completion (16 Nov 2026, local)

| Requirement | Implementation | Test |
|---|---|---|
| Header and footer menus, dropdowns and sub-items | Admin › **Menus & Branding**: header menu, footer columns, homepage service tiles, header button. Two levels (dropdown/column → items) | `MenuBuilderTest` |
| Ordering, show/hide | ↑/↓ buttons (re-number siblings), display order field, Show/Hide toggle | `MenuBuilderTest` |
| English and Bengali labels | Both per item; Bengali shown when the site language is বাংলা. Seeded from the existing translations | `MenuBuilderTest` |
| Internal links | Chosen from a vetted list of public pages, the 15 service pages and the enquiry forms; anchors allowed (`#consultation`). Admin pages cannot be linked | `MenuBuilderTest` |
| External links | `https://` only; `http`, `javascript:` and malformed URLs are rejected. Optional new tab with `rel="noopener noreferrer"` | `MenuBuilderTest` |
| Broken links | Cannot be saved; an item whose page later disappears is hidden on the website and flagged "Broken target" in admin | `MenuBuilderTest` |
| Duplicates | Same label (case-insensitive) or same link at the same level is rejected | `MenuBuilderTest` |
| Role-based visibility | Everyone / signed-out / signed-in / selected roles; items also hide when their Feature Control switch is off | `MenuBuilderTest` |
| Mobile navigation | Uses the same menu markup, so mobile and desktop always match | crawl, gallery |
| Permission | New `menus.manage` (super-admin, admin). Content editors and customers get 403. Every change is written to the activity log | `MenuBuilderTest` |
| Safe default | When a location has no items the built-in menu is shown. `MenuSeeder` copies today's menus and never overwrites admin edits on re-run | `MenuBuilderTest` |
| Branding | Header tagline (EN/BN) on Menus & Branding; header button label and destination via "Header button"; tile labels and icons via "Homepage service tiles". Logo, site name, footer text, contact and social links in Admin › Homepage. API messages and other labels in Translations | `MenuBuilderTest` |
| Logo | The original logo is the default in the site, auth and admin layouts. An uploaded logo is used only if its file exists; nothing is replaced automatically | `MenuBuilderTest` |
| API status consistency | Hotels, tours and visa pages previously said "Live provider connected" whenever a provider was *configured*. They now use the same rule as the badges: LIVE only after a passing connection test. The signed-in flight search page now shows WAITING FOR API with the flight-fare enquiry | `CompleteFeaturePagesTest`, `Hotel/Tour/VisaFoundationTest` |

**Verification:**
- Full suite **908 passed, 0 failed** (6,675 assertions).
- Production build passes.
- 52 migrations applied, 0 pending (1 new: `menu_items`). 320 routes.
- Crawl with the managed menus seeded (guest 114, student 124, agent 126, owner 251 pages): 0 broken links, 0 empty links, 0 dead buttons, 0 overflow. 49 menu admin pages all 200.
- Gallery: 116 previews (232 screenshots) including 4 new Menus & Branding pages; 0 horizontal overflow.

## Items that cannot be completed without the owner or a provider (honest list)

1. **No provider is live.** Live flights, hotels, tours, visa information, payments, maps, SMS, WhatsApp, push and transport booking each need a chosen provider, credentials and, except flights, a connection test that still has to be built. Until then they correctly show WAITING FOR API.
2. **Content waiting for the owner:** guides, company profile, mission/vision, team, office photos, destinations, restaurants, success stories, offers, forum categories, rewards terms and contact directory numbers. Their pages show honest empty states.
3. **Menus on the live server:** the menu builder needs the new migration and `MenuSeeder` (included in `ProductionSeeder`) at the next approved deployment. Until then, and whenever a menu location has no items, the built-in menus are shown.
4. **Mobile app:** no app build exists. The API foundation is ready.
5. **Translations:** machine-assisted. Native and legal review is still required.
