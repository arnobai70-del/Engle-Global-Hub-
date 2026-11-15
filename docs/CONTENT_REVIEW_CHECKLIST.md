# Content Review Checklist

Everything below is in the local CMS as **unpublished drafts**, created by `database/seeders/ContentDraftSeeder.php` (re-runnable; it never overwrites edits or publishes anything). Nothing is visible to the public until an owner reviews it, marks it **Approved** and publishes it.

How to review: Admin › **Content Approval** (owner only) shows every unpublished entry with EN/BN previews, sources and blockers; **Approve** (does not publish) or **Request changes**. Then publish from Admin › Website Content → the entry → **Publish**. Full owner checklist: `docs/OWNER_APPROVAL_CHECKLIST.md`.

Facts were checked against official sources on **15 Nov 2026**. "Fetched" means the page itself was opened and read. "Not verifiable" means the page could not be read, so the guide stays locked (it cannot be approved) until a person confirms it.

Legend: **A** drafted from research · **B** needs company information · **C** needs evidence or consent · **D** needs an external provider.

## 1. Singapore study guides (7 drafts, English and Bengali)

| Guide (slug) | Review status | Sources | Before approving |
|---|---|---|---|
| Student's Pass requirements (`students-pass-guidance`) | Needs owner review | All fetched: ICA IHL, PEI, completion of formalities (06–09 Nov 2026), ICA visa list (28 Oct 2026) | Confirm the "How Eagle Global Hub can help" text |
| College admission (`college-admission`) | Needs source verification | Fetched: ICA PEI, MOE DAE (06 Jul 2026), MOE TG. **Not verifiable: CPE** (site unreachable) | Open the CPE links; if they match, set "Needs owner review" |
| University admission (`university-admission`) | Needs owner review | All fetched: MOE admissions (03 Jul 2026; wording corrected), ICA IHL, MOE TG and bond (23 Jun 2026) | Read and approve |
| Course selection (`course-selection`) | Needs source verification | Fetched: ICA, MOE TG. **Not verifiable: CPE EduTrust** | Confirm the CPE EduTrust link |
| Tuition fees and costs (`tuition-fees`) | Needs owner review | All fetched: ICA fees, MOE TG and bond | Gives no tuition figures, by design |
| Accommodation (`student-accommodation`) | Needs source verification | **Not verifiable: HDB** (rules load dynamically) | Confirm on hdb.gov.sg or remove that section; confirm "we do not act as a property agent" |
| Pre-departure (`pre-departure`) | Needs owner review | All fetched: ICA completion (06 Nov 2026), SG Arrival Card (28 Oct 2026; wording corrected, MyICA mention removed) | Confirm the air-ticket service mention |

The "Study in Singapore from Bangladesh" topic is covered by the existing landing page `/study-in-singapore`. No duplicate guide was created for the same search intent; the landing page links to every published guide.

**Never add:** processing-time promises beyond ICA's own wording, tuition figures not taken from the institution's official page, approval rates, "guaranteed" outcomes, or partnership claims.

## 2. Content-dependent PDF items (24 items in the matrix)

| Item | Type | Draft in CMS | What the owner must do |
|---|---|---|---|
| Team (5 PDF members) | C | 5 unpublished profiles (names and roles only) | Verify names and roles, add real photos and bios if wanted, then publish |
| Mission | A/B | Page section `mission` (EN+BN), proposed | Edit or approve, then publish |
| Vision | A/B | Page section `vision` (EN+BN), proposed | Edit or approve, then publish |
| কেন আমরা ব্যতিক্রম? | A/B | Page section `why-different` (EN+BN), proposed; each point matches a real system feature | Confirm each point matches how the business works |
| Company Profile | B | `eagle-global-hub-ltd` (EN+BN): only the name, office address and PDF service list | Provide year established, trade licence number, other registrations, certificates (upload to Media Library) and history; super-admin approval required |
| Export & Import | B | Business service `export-import` (EN+BN), general description | Describe the real scope; mention licences (e.g. IRC/ERC) only once verified. The enquiry form already works |
| Civil & Construction | B/C | Business service `civil-construction` (EN+BN); no projects | Real completed projects with photos and permission; each needs owner approval |
| Success History | C | None | Real stories, each with written consent (record a reference and date in the entry) |
| Office Environments | C | None | Upload real office photos to Media Library › Office gallery |
| Explore Bangladesh: Cox's Bazar, Sylhet, Kuakata (4 items) | A | Descriptions with official sources (Parjatan Corporation, Tourism Board, Sylhet district); still unpublished | Add photos with alt text, review, publish |
| Discover / Get Inspired | A | Two articles: Cox's Bazar, Sylhet (EN+BN) | Add licensed photos, review, publish |
| Places You May Like | A | Works once destinations are published | Choose recommendations on each destination |
| Restaurants | B | None. No listings were invented. | Add real restaurants (name, location, contact, optional coordinates) |
| Rewards | B | "Eagle Rewards (proposed)": unpublished terms skeleton, no earning rules | Decide what points are worth, the earning rules, expiry and notice period; legal review; then publish |
| Forums | B | 4 categories created **closed** with approval required | Agree the moderation guidelines below, then open categories |
| Contact Directory (10 numbers + 1 IP line) | B | None. No numbers were invented. | Enter the real numbers and mark the public ones |
| FAQs | A | 4 FAQs (guarantee, tracking, document privacy, payments) | Review, publish |
| Map | D | — | Choose a maps provider (OpenStreetMap links work now) |

## 3. Forum moderation guidelines (proposed)

1. No personal data: passport, NID, phone and bank numbers. The system masks emails, phone numbers and passport-style numbers automatically, but moderators remove anything it misses.
2. No advertising, recruitment or "visa guarantee" offers from third parties.
3. Staff answers about immigration must point to official sources (ICA, MOE, the institution) and must not promise outcomes.
4. Keep "Approve posts first" switched on for every category at launch. Review the queue at least once per working day.
5. Hide, rather than delete, posts that break the rules, so the moderation history stays in the activity log.
6. Reported posts must be reviewed within 2 working days.

## 4. Bengali review

- All 366+ interface phrases and every bilingual draft (416 items audited 15 Nov 2026: 0 mechanical issues) are **machine-assisted drafts** and need a native-speaker review (Admin › Translations and each content entry).
- Terminology used consistently: স্টুডেন্টস পাস (Student's Pass), ইন-প্রিন্সিপাল অ্যাপ্রুভাল (IPA), ICA in Latin letters, কনসালটেন্ট, ইনটেক.
- Brand names stay as written: "Egabe B2B". "ঈগল গ্লোবাল হাব লিমিটেড" is used for the company name in Bengali text. Owner: confirm the preferred Bengali spelling.
- Immigration and legal wording (guides, rewards terms, Terms page) should also get a legal review before publishing.

## 5. Claims the owner should confirm on existing pages

- `/study-in-singapore` says "Request a free consultation". Confirm consultations are free, or edit the wording in Admin › Translations (English).
- The footer says "Your trusted global travel and visa partner…" (existing homepage CMS). Confirm.
