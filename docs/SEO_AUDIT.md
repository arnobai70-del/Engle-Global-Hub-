# SEO Audit — Canonical, Language and Metadata

Date: 16 Nov 2026. The audit was run on the local build, uncommitted and not deployed. Production domain: `https://eagleglobalhub.com`.

## 1. Canonical policy

| Page type | Canonical | Robots | hreflang |
|---|---|---|---|
| Translated public pages* (English) | Own URL, no query string | `index,follow` | `en` → URL, `bn` → URL`?lang=bn`, `x-default` → URL |
| Translated public pages* (Bengali, `?lang=bn` or Bengali session) | Own URL`?lang=bn` (self-referencing) | `index,follow` | Same three links |
| Other public pages | Own URL (English) | `index,follow` | None |
| Published guides with Bengali text | Set by the guide page (unchanged) | `index,follow` | Set by the guide page |
| Guides index with a search or topic filter | Clean index URL | `noindex,follow` (filtered results are not separate pages) | None (only indexable pages get hreflang) |
| Login, Register, Forgot password | Own clean URL (query string such as `?lang=bn` removed) | `noindex,follow` | None |
| Reset password (URL contains a token) | **None**, so the token is never repeated in a tag | `noindex,follow` | None |
| Portals and admin | Not indexed | Disallowed in `robots.txt`, not in the sitemap | None |

\* Translated public pages:
- Home, Study in Singapore, Singapore Guides, Institutions, Travel Services
- Visa, Work Visa, Hotels, Tours
- Support, Terms, About
- Explore Bangladesh, Offers
- service enquiry forms

The language-aware canonical and hreflang are generated in one place (`layouts/site.blade.php`). This replaces the copy that only the Study in Singapore page had.

**Absolute HTTPS:** every canonical, `og:url` and hreflang URL is built with Laravel's `url()` / `route()`. In production, `APP_URL=https://eagleglobalhub.com` and the app forces the `https` scheme (`AppServiceProvider`), so all of them are absolute HTTPS URLs on the real domain. Locally they show `http://127.0.0.1`.

**Login and Register** never point to unrelated public pages. Each points to itself, or has no canonical at all (reset password).

## 2. Titles, descriptions and Open Graph

| Check | Result |
|---|---|
| Double-escaped titles and descriptions ("&amp;amp;") | Fixed in the previous round. A test (`SiteRedesignTest`) guards it |
| `og:title`, `og:description`, `og:url`, `og:image`, Twitter tags | Present on public pages. `og:url` equals the canonical |
| Auth pages | Now have `og:title`, `og:description` and `og:url` (clean URL; none on reset password) |
| Unique titles on public pages | Yes (checked across all public pages) |
| One `<h1>` per page | Yes |

## 3. Sitemap and robots.txt

- `robots.txt` is unchanged. It disallows `/admin`, `/dashboard`, `/account`, `/my-applications`, `/my-enquiries`, `/notifications`, `/agent` and `/bookings`, and points to the absolute sitemap.
- Login and Register are **not** blocked in robots.txt on purpose. Search engines must be able to read their `noindex`.
- The sitemap is unchanged except for the new pages:
  - `/travel-services` is always listed.
  - `/study-in-singapore/guides` is listed only when at least one guide is published.
  - `/study-in-singapore/institutions` is listed only when at least one institution is publicly listed.
- Drafts, previews, portals, admin and auth pages never appear (tested).

## 4. Verification

| Check | How | Result |
|---|---|---|
| Auth pages `noindex,follow`, clean self-canonical, `og:url`; no canonical on reset password | `BengaliPagesAndAuthSeoTest`, plus live `curl` of `/login`, `/login?lang=bn`, `/register`, `/forgot-password` | Pass |
| Bengali canonical `?lang=bn` plus hreflang en/bn/x-default | `NewPublicPagesTest`, plus live check of `/travel-services?lang=bn` | Pass |
| English canonical has no query string | `NewPublicPagesTest` (Institutions) | Pass |
| Filtered guide results are `noindex` | `NewPublicPagesTest` | Pass |
| Sitemap lists new pages only with public content | `NewPublicPagesTest` | Pass |
| Existing SEO tests (titles, structured data, sitemap, redirects) | `SeoMetadataTest`, `HomepageSeoNewsletterTest`, `WebsiteCmsTest` | Pass (see the final test result in `docs/UI_FINAL_REVIEW.md`) |

## 5. After deployment (owner or developer)

1. Confirm `APP_URL=https://eagleglobalhub.com` in the production `.env`.
2. Open `/login` and `/study-in-singapore?lang=bn` and view the page source. The canonical and hreflang tags must start with `https://eagleglobalhub.com`.
3. Submit `https://eagleglobalhub.com/sitemap.xml` in Google Search Console and Bing Webmaster Tools.
4. Optional: redirect `www.eagleglobalhub.com` to the main domain at the web server (see `docs/DEPLOYMENT.md` §11) to avoid duplicate hosts.
