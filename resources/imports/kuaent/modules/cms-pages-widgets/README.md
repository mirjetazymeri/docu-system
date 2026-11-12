# CMS pages & widgets

CMS pages, page-builder widgets, header/footer menus, and home widgets for the storefront API.

This module is not a Laravel package. It is the pages/widgets slice of `TestDocu-admin`: admin CRUD, JSON config on widgets, `page_widgets` placements, and public `GET /api/v1` endpoints.

## What it does

- **Pages** — hierarchical CMS pages (`parent_id`, `slug`, `path`), publish state, SEO fields, nav flags for header/footer menus, optional footer column (`footer_section`).
- **Widgets** — reusable blocks with a `type` string and JSON `config`. Admin types are the only live types (see below).
- **Placements** — attach widgets to a page slot (`page_widgets`), reorder, toggle active.
- **Menus** — public header/footer trees built from published pages with `show_in_header` / `show_in_footer`.
- **Home widgets** — `GET /api/v1/widgets/home` lists active widgets that are not `hide_on_home`.

## Live widget types

Source of truth: `Admin\WidgetController::getTypes()`. Do not treat `config/pagebuilder.php` or `PageSeeder` as the type list.

| `type` | Admin label | Runtime resolver |
|---|---|---|
| `featured_products` | Featured Products (manual selection) | none (IDs stay in config) |
| `random_products` | Random Products (new set on refresh) | `RandomProductWidgetResolver` |
| `trending_products` | Trending Products (from analytics) | `TrendingProductWidgetResolver` |
| `products_by_attribute` | Products by Attribute | none |
| `products_by_category` | Products by Category | none |
| `category_list` | Category List | none |
| `attribute_options_list` | Attribute Options List | none |
| `carousel` | Image Carousel | none (image URLs resolved) |
| `video` | Video Block | none |
| `html` | Custom HTML | none |
| `promotions_all` | All Promotions (auto-updates) | `PromotionWidgetResolver` |
| `promotions_selected` | Selected Promotions | `PromotionWidgetResolver` |
| `contact_form` | Contact Form | `WidgetRenderer` adds `submit_endpoint` |

`WidgetRenderer` runs for every type: locale merge, image/slide URL expansion, then the type-specific appenders above.

## Channel and locale

- Admin pages and widgets sync to the admin user’s **current channel** (`page_channel`, `widget_channel`).
- Public API uses `channel.api` + `locale.api`. Pages are resolved with `Page::published()->forChannel($channelId)`.
- Copy overlays: page translations → then `page_channel_contents` on the page payload path (see [api.md](api.md) for resource vs controller). Widget copy: `Widget::localizedTitle()` / `localizedConfig()`.

## Publish

Pages use `status` (`draft` | `published` | `archived`) and optional `published_at`. The public page/menu/SEO/sitemap paths require `status = published` and `published_at` null or in the past.

There is **no** draft page preview API. Widget type `html` has a config flag `enable_preview` for the storefront HTML widget, not page preview.

See [workflows.md](workflows.md).

## Docs in this folder

| File | Contents |
|---|---|
| [architecture.md](architecture.md) | Request flow, cache, overlay order |
| [models.md](models.md) | Eloquent models and relations |
| [database.md](database.md) | Tables, migrations, seeder status |
| [controllers.md](controllers.md) | Admin + API controllers |
| [services.md](services.md) | `app/Services/Cms/*`, `FooterSections` |
| [requests-validation.md](requests-validation.md) | FormRequests and inline rules |
| [routes.md](routes.md) | Admin and API routes |
| [api.md](api.md) | Public JSON contracts |
| [dependencies.md](dependencies.md) | channels-locales, catalog-products, promotions |
| [tests.md](tests.md) | Test coverage (none dedicated) |
| [workflows.md](workflows.md) | Publish, menus, home widgets |

## Out of scope (not this module)

- Theme demo controllers `PagesController` / `WidgetsController` and `GET /widgets` (non-admin).
- Settings footer **column content** (contact lines) — uses `FooterSections` but is owned by settings. This module only uses `FooterSections` for footer **section keys** on pages and menus.
- `config/pagebuilder.php` — unused; not loaded anywhere in PHP.
