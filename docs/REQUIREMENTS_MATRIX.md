# PDF Requirement Compliance Matrix

Source: *Travel & Visa Agency Business Requirements.pdf* (2 pages), the owner's implementation briefs, and the owner-approved definitions of the 12 previously unclear items (round 4).
Last updated: 2026-11-15, after round 5 (content drafts and production readiness). All work is local and uncommitted on `main`.

## Status categories (stricter than earlier rounds)

| Code | Meaning |
|---|---|
| **FULL** | Implemented, tested, and working for the public or staff today |
| **OWNER** | Implemented and tested, but shows nothing publicly until the owner adds or verifies content or configuration |
| **PARTIAL** | Some functionality exists; the gaps are listed |
| **API** | Waiting for an external provider (adapter and credentials) |
| **NONE** | Not implemented |

A backend foundation alone is never counted as FULL.

## Compliance summary (same 97 PDF items as before)

| Category | Count | Share |
|---|---|---|
| FULL | 71 | 73.2 % |
| OWNER | 24 | 24.7 % |
| PARTIAL | 1 | 1.0 % |
| API | 1 | 1.0 % |
| NONE | 0 | 0 % |

95 of 97 items (97.9 %) are implemented. 71 (73.2 %) work publicly today without further owner action.

Round 3 reported 75 "done" because it counted owner-content items as done. Under this round's stricter rule, items such as team profiles and Mission & Vision now count as OWNER, so the FULL count is lower even though more is built.

How the 97 are counted: 11 services, 34 destination-country entries, 5 team members, 35 website modules, 4 Explore Bangladesh items and 8 system-portal items.

### Round 5 (15 Nov 2026): what blocks the 24 OWNER items

The counting method is unchanged. Drafts are **not** counted as FULL, because nothing is published until the owner approves it. Details: `docs/CONTENT_REVIEW_CHECKLIST.md`. The owner reviews everything in Admin › Content Approval (`/admin/content-approvals`, super-admin only; approving never publishes). Owner checklist: `docs/OWNER_APPROVAL_CHECKLIST.md`.

| Blocker | Count | Items |
|---|---|---|
| **Content drafted; awaiting owner approval and publishing** | 11 | Mission & Vision (8), কেন আমরা ব্যতিক্রম? (12), Export & Import (9), Discover (24), Forums (26, categories prepared but closed), Get Inspired (28, two articles), Places You May Like (29, needs published destinations), Explore Bangladesh: Cox's Bazar, Sylhet, Kuakata and the listing (4) |
| **Needs company information** | 5 | Affiliator numbers (1), Company Profile details and licences (4), Civil & Construction scope (10), Restaurants (22), Rewards earning rules (23) |
| **Needs evidence, consent or verification** | 8 | Team, 5 people (verify names and roles), Our Employees (13), Success History (5, consent), Office Environments (14, real photos) |

Also prepared this round:
- Seven bilingual Singapore study guides with official ICA, MOE, CPE and HDB sources, a review status and a last-checked date. These support the Student Visa service (already FULL) and are not separate PDF items.
- Four FAQs.
- Editable English interface text (Admin › Translations).

Test files referenced below live in `tests/Feature/`.

## Section 2: Services (11 FULL)

| Requirement | Status | Admin | Public | API dependency | Tests | Limitations |
|---|---|---|---|---|---|---|
| Student Visa | FULL | Student Enquiries, Applications, Courses, Appointments, Payments, Upcoming Success | `/study-in-singapore`, `/my-applications` | None (manual statuses) | `Consultancy/*`, `PaymentRecordTest` | No institution API |
| Tourist, Business and Job/Work Visa | FULL (manual) | Service Enquiries | `/enquiries/{tourist,business,work}-visa` | Sherpa (visa info), optional | `ServiceEnquiryTest` | Visa information is not live |
| Air Ticket | FULL | Flight Enquiries | Flight search and enquiry | Duffel for live search and booking | `Flight/*` | Live booking needs Duffel credentials |
| Hotel Booking | FULL (manual) | Service Enquiries | `/enquiries/hotel-booking` | Duffel Stays | `ServiceEnquiryTest` | No live availability |
| Tour Packaging | FULL (manual) | Service Enquiries (holiday, group, private) | `/enquiries/tour-package` | Viator | `BusinessModulesTest` | No live availability |
| Trains, Bus, Cars, Bike | FULL (manual) | Service Enquiries | `/enquiries/{train,bus,car,bike}` | None chosen | `ServiceEnquiryTest` | Manual only |

## Sections 3–5: Destination countries (34 FULL)

Seeded in `ServiceDestinationSeeder` and managed in Admin › Services & Destinations. They drive the student, tourist and work-visa forms. Tests: `Admin/ServiceDestinationManagementTest`.

## Section 6: Team (5 OWNER)

Five unpublished drafts are managed in Admin › Team, and published profiles appear on the About page (`TeamMemberManagementTest`, `WebsiteCmsTest`). **Owner action:** verify names and roles, then publish.

## Section 7: The 35 website modules

| # | Module | Status | Admin | Public | API | Tests | Limitations or owner action |
|---|---|---|---|---|---|---|---|
| 1 | Affiliator contact format | OWNER | Contact Directory (max 10 phones + one 24/7 IP line) | Support page directory | None | `BusinessModulesTest` | Enter the real numbers |
| 2 | Student Application Form | FULL | Student Enquiries | `/study-in-singapore` | None | `Consultancy/*` | |
| 3 | Tourist Application Form | FULL | Service Enquiries | `/enquiries/tourist-visa` | None | `ServiceEnquiryTest` | |
| 4 | Company Profiles | OWNER | Website Content › Company Profile (super-admin approval required) | `/company` | None | `ContentModuleTest` | Write and approve the profile |
| 5 | Success History | OWNER | Website Content › Success Story (consent required) | `/success-stories` | None | `ContentModuleTest` | Real stories with consent |
| 6 | Pending Files | FULL | Applications filtered by status | Internal | None | `StudentApplicationWorkflowTest` | |
| 7 | Upcoming Success | FULL | Upcoming Success (internal pipeline, follow-up dates, scoped by assignment) | Internal only | None | `BusinessModulesTest` | Next steps are shown, never predicted outcomes |
| 8 | Mission & Vision | OWNER | Settings › company | About page | None | `WebsiteCmsTest` | Write the text |
| 9 | Export & Import | OWNER | Website Content › Business Service (category `export-import`); Service Enquiries | `/business-services`, `/enquiries/export-import` | None | `ContentModuleTest`, `BusinessModulesTest` | Enquiries work now; the service description needs owner text. No licence claims. |
| 10 | Civil & Construction Work | OWNER | Business Service (`civil-construction`), Projects (approval required); Service Enquiries | `/business-services`, `/enquiries/civil-construction` | None | as above | Enquiries work now; projects need owner approval |
| 11 | Why Choose Us | FULL | Homepage CMS | Homepage | None | existing | |
| 12 | কেন আমরা ব্যতিক্রম? | OWNER | Settings › company `why_choose_us_bn` | About page | None | `WebsiteCmsTest` | Write the text |
| 13 | Our Employees | OWNER | Team | About page | None | `TeamMemberManagementTest` | Verify and publish |
| 14 | Our Office Environments | OWNER | Media Library › Office gallery | About page | None | `WebsiteCmsTest` | Upload real photos |
| 15 | Egabe B2B | FULL | Agents (partner type, approval) | `/b2b/register`, `/agent` (student and service referrals) | None | `BusinessModulesTest` | Uses the existing login; no second auth system |
| 16 | Holiday Package | FULL | Service Enquiries (holiday category), Offers | `/enquiries/tour-package` | Viator optional | `BusinessModulesTest` | No package catalogue with prices |
| 17 | Promotion | FULL | Offers | `/offers` | None | `OffersAndReviewsTest` | |
| 18 | Business Class | FULL | Flight Enquiries (cabin filter) | `/enquiries/business-class` → flight enquiry | Duffel for live search | `BusinessModulesTest` | Manual quote without an API |
| 19 | About Us | FULL | Website Content › About intro | `/about` | None | `ContentModuleTest` | |
| 20 | Support Center | FULL | Support intro, FAQs, contact directory | `/support` | None | `ContentModuleTest` | |
| 21 | Search All | FULL | — | `/search` (published content only) | None | `WebsiteCmsTest`, `ContentModuleTest` | Basic keyword match |
| 22 | Restaurants | OWNER | Website Content › Restaurant (location, coordinates) | `/restaurants` | Maps optional | `ContentModuleTest` | Add real listings |
| 23 | Rewards | OWNER | Rewards (programmes, ledger, audit) | `/rewards` | None | `CommunityAndRewardsTest` | Disabled until terms and earning rules are published |
| 24 | Discover | OWNER | Destinations, Get Inspired | `/explore-bangladesh`, `/get-inspired` | None | `ExploreBangladeshTest` | Publish destinations and articles |
| 25 | Reviews | FULL | Reviews moderation | `/reviews` | None | `OffersAndReviewsTest` | |
| 26 | Forums | OWNER | Forums (categories, approval queue, reports, hide, lock) | `/forums` | None | `CommunityAndRewardsTest` | Create the first categories |
| 27 | Sign In | FULL | — | `/login` | None | existing | |
| 28 | Get Inspired | OWNER | Website Content › Get Inspired | `/get-inspired` | None | `ContentModuleTest` | Write articles |
| 29 | Places You May Like | OWNER | Destination recommendations | Destination pages | None | `BusinessModulesTest` | Needs published destinations |
| 30 | Group Tours | FULL | Service Enquiries | `/enquiries/tour-package` (group) | None | `BusinessModulesTest` | |
| 31 | Private Tours | FULL | Service Enquiries | `/enquiries/tour-package` (private) | None | as above | |
| 32 | Map | API | Coordinates on content | OpenStreetMap links only | Maps provider (`MapProvider` contract) | `ContentModuleTest` | No embedded map |
| 33 | Hotels and Homes | FULL (manual) | Service Enquiries | `/enquiries/hotel-booking` | Duffel Stays | `ServiceEnquiryTest` | No live inventory |
| 34 | Deals | FULL | Offers | `/offers` | None | `OffersAndReviewsTest` | |
| 35 | APP | PARTIAL | — | `/api/v1` (tokens, applications, notifications); `docs/API.md` | Push provider for notifications | `ApiAndPaymentReadinessTest` | No mobile app exists; this is only the API foundation |

## Section 8: Explore Bangladesh (4 OWNER)

The catalogue, public pages, structured data and "Places You May Like" all work. Cox's Bazar, Sylhet and Kuakata are drafts. **Owner action:** verified text and photos, then publish. Tests: `ExploreBangladeshTest`, `BusinessModulesTest`.

## Section 9: System portal (8 FULL)

| Item | Status | Notes |
|---|---|---|
| English | FULL | |
| Bengali | FULL | Working EN/বাং switch. All 279 public and portal interface keys are translated; `TranslationCoverageTest` fails if one is missing. Admin-editable in Translations. **Needs native-speaker review.** Admin screens are English only. |
| Foreigner Support | FULL | `/enquiries/foreigner-support` with categories, staff assignment, status tracking and customer notifications |
| Special Offers, Agent / Student / Tourist Login, About | FULL | |

## Singapore SEO (round 4)

Seven guide topics are drafts (`SingaporeGuideSeeder`). Admin controls title, slug, canonical URL, SEO title and description, cover (Open Graph), related links and publishing. Publishing requires an official https source. The public page shows Article and Breadcrumb structured data, the sources, and an `hreflang` Bengali version. Published guides appear in the sitemap and on the Singapore page. **Owner action:** write verified content for each guide.

## API and integration readiness

| Integration | Status |
|---|---|
| Flights (Duffel) | Adapter exists; connection test; needs credentials |
| Hotels (Duffel Stays), Tours (Viator), Visa info (Sherpa) | Adapters exist; need credentials |
| Payments | Offline recording works. `PaymentGateway` contract, signed and idempotent webhook endpoint are ready. **No gateway adapter.** |
| SMS, WhatsApp, Maps | Contracts ready, bound to "unavailable"; **no provider adapter** |
| Email | Works through Laravel mail; set `MAIL_*` |
| Analytics | Meta Pixel via Settings |
| Universities / admission partners | No API; manual catalogue and statuses |

## Outstanding owner actions

1. Content: team verification; Mission & Vision; কেন আমরা ব্যতিক্রম?; company profile (with approval); export & import and civil & construction descriptions; restaurants; Get Inspired articles; success stories with consent; Singapore guides with official sources; destination texts and photos; office photos; contact directory numbers; forum categories; rewards terms.
2. Native-speaker review of the Bengali translations.
3. Choose providers (payment gateway, SMS, WhatsApp, maps, push notifications) if online versions are wanted.
