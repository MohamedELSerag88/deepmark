# Marketing CMS (Scope C, Bilingual)

Make the deepmark-app marketing site fully dynamic (Scope C): bilingual CMS for site settings, home sections, blogs, FAQs, pricing, contact — portfolio projects reuse **BrandNameSuggestion**.

## Decisions locked

- **Scope C:** Site settings + Home sections + Blogs + FAQs + Pricing + Contact + Portfolio via BrandNameSuggestion
- **Bilingual:** `_en` / `_ar` columns
- **Projects:** Use existing `BrandNameSuggestion` model (marketing fields + `is_marketing_featured`) — no separate `marketing_projects` table
- **Controllers:** One controller per model (public + admin)
- **Images:** URL fields
- **Patterns:** Eloquent + Form Requests + API Resources + `MarketingHomeService`

## Public API (`/api/mobile/v1/marketing/*`)

| Endpoint | Controller |
|----------|------------|
| `GET home` | `HomeController` |
| `GET settings` | `SiteSettingController` |
| `GET projects` / `GET projects/{id}` | `BrandNameSuggestionController` |
| `GET blogs` / `GET blogs/{slug}` | `BlogPostController` |
| `GET faqs` | `FaqController` |
| `GET pricing` | `PricingPackageController` |
| `POST contact` | `ContactSubmissionController` |

## Admin API (`/api/admin/marketing/*`)

- Settings, HomeSections, Blogs, Faqs, Pricing, ContactSubmissions — full CRUD where applicable
- Projects: list/show/update `BrandNameSuggestion` marketing fields (feature for portfolio)

## Tables

- `site_settings`, `home_sections`, `blog_posts`, `faqs`, `pricing_packages`, `contact_submissions`
- `brand_name_suggestions` + marketing columns (`is_marketing_featured`, `marketing_*`)
