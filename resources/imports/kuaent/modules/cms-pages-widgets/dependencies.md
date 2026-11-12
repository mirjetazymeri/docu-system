# Dependencies

This module is not a Composer package. Cross-module calls are Eloquent / helpers / HTTP routes.

## channels-locales

**Used throughout.**

| Mechanism | Where |
|---|---|
| `page_channel` / `widget_channel` | Admin sync to `auth()->user()->current_channel_id` only |
| `Page::forChannel` / `forCurrentChannel` | Admin CRUD, API page/menu/seo/sitemap, attach widget |
| `Widget::forChannel` / `forCurrentChannel` | Admin widgets; API page widget filter; API widget show 404 |
| `app('currentChannel')` | All API CMS controllers and resolvers |
| `channel.api` (`ResolveApiChannel`) | API + sitemap |
| `locale.api` | API locale |
| `channel_locale_codes()` | Admin page/widget translation tabs |
| `PageTranslation` / `WidgetTranslation` | Locale overlay; fallback `config('app.fallback_locale')` |
| `page_channel_contents` | Per-channel copy on pages |
| `setting('footer.sections')` | Channel-aware settings via `SettingService` |

`GET /widgets/home` and `GET /widgets` (placement) do **not** apply the same widget-channel filter as page show / widget show.

## catalog-products

Resolvers that **query** catalog:

| Resolver | Reads | Writes |
|---|---|---|
| `RandomProductWidgetResolver` | `product_projections` (`channel_id`, `locale`, `user_group_id=0`, optional `in_stock`), `product_categories` | no |
| `TrendingProductWidgetResolver` | same projections + `product_categories`; ranking from `storefront_events` | no |

Admin types that **store IDs only** (no CMS resolver; storefront hydrates via products-projection / categories / attributes):

- `featured_products` → `config.product_ids`
- `products_by_category` → `category_id`, `products_by_category`
- `products_by_attribute` → `attribute_id`, `attribute_option_id`, `products`
- `category_list` → `category_id`
- `attribute_options_list` → `attribute_id`, `attribute_option_id`

Admin UI loads those IDs through `Admin\LookupController` (`products`, `categories`, `attributes`, `attributeOptions`).

`TrendingProductWidgetResolver` also depends on **storefront events** (`StorefrontEvent`: consented product `click` / `add_to_cart`), not on the catalog write path.

## promotions

| Caller | Promotion API |
|---|---|
| `PromotionWidgetResolver` | `Promotion::forChannel`, `active()`, `with('translations')`, `withCount('items')`; `PromotionResource`; `max(updated_at)` stamp |
| Admin `promotions_selected` | `config.promotion_ids` via `lookups/promotions` |
| Admin `promotions_all` | `active_only`, `limit` only — list resolved at read time |

Does not write promotions. Does not call `PromotionController` HTTP routes; the storefront may still use `GET /promotions/{slug}/products` after a card click.

## Other runtime deps (not the three named modules)

| Dep | Use |
|---|---|
| `POST /api/v1/contact` + `ContactFormRequest` + `MailService` | `contact_form` submit |
| `SeoController` / `SitemapController` | published page URLs |
| Laravel `Cache`, `Storage` disk `public` | widget/page/menu cache; widget image uploads under `widgets/` |
| Spatie-style permissions `page_*` / `widget_*` | admin |

## What does **not** depend on what

- `carousel`, `video`, `html` resolvers: none. HTML `enable_preview` is a boolean in config only.
- `config/pagebuilder.php`: unused — **not** a dependency of runtime code.
- `PageSeeder`: unused — would have needed Category/Product plus missing section models.

## Resolver → module matrix

| Widget type | channels-locales | catalog-products | promotions | storefront_events |
|---|---|---|---|---|
| `featured_products` | channel on widget/page | IDs in config only | — | — |
| `random_products` | channel + locale on projections | **query** | — | — |
| `trending_products` | channel + locale on projections | **query** | — | **query** |
| `products_by_category` | channel on widget/page | IDs in config only | — | — |
| `products_by_attribute` | channel on widget/page | IDs in config only | — | — |
| `category_list` | channel on widget/page | IDs in config only | — | — |
| `attribute_options_list` | channel on widget/page | IDs in config only | — | — |
| `carousel` | channel + locale copy | — | — | — |
| `video` | channel + locale copy | — | — | — |
| `html` | channel + locale copy | — | — | — |
| `promotions_all` | `Promotion::forChannel` | — | **query** | — |
| `promotions_selected` | `Promotion::forChannel` | — | **query** | — |
| `contact_form` | channel + locale copy | — | — | — |
