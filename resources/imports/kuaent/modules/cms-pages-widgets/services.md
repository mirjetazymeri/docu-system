# Services

All CMS resolvers live in `app/Services/Cms/`. Footer parsing is `app/Support/FooterSections.php`.

## `WidgetRenderer`

Injects the three product/promotion resolvers.

### `widgetKey(Widget $widget): string`

`{type}.{layout}` with `layout` default `1x1`. If `config.is_carousel` is truthy, appends `.carousel`.

Examples: `featured_products.1x4.carousel`, `category_list.2x3`, `carousel.1x1`.

### `resolve(Widget $widget, array $ctx = []): array`

`$ctx['locale']` or app locale.

1. `localizedConfig($locale)`.
2. Carousel slides: `image` → `asset('storage/' . path)`; drop `keep_image` / `existing_image`.
3. Flat `images[]`: `{ url, alt }` from `images_alt`; unset `images_alt`.
4. `appendPromotions` / `appendRandomProducts` / `appendTrendingProducts` / `appendContactForm`.

### Type appenders (only these types)

| Widget `type` | What is added |
|---|---|
| `promotions_all`, `promotions_selected` | `config.promotions` from `PromotionWidgetResolver::resolveList` |
| `random_products` | `config.product_ids` from `RandomProductWidgetResolver::pickIds` (current channel + locale) |
| `trending_products` | `config.product_ids` from `TrendingProductWidgetResolver::pickIds` |
| `contact_form` | `config.submit_endpoint` = `url('/api/v1/contact')` |

No resolver classes exist for `featured_products`, `products_by_category`, `products_by_attribute`, `category_list`, `attribute_options_list`, `carousel`, `video`, `html`. Those types return merged config (plus image URL expansion where applicable).

## `PromotionWidgetResolver`

**Module called:** promotions (`App\Models\Promotion`, `PromotionResource`).

- `promotions_selected` — `config.promotion_ids` order preserved; `Promotion::forChannel($channelId)->active()->whereIn('id', $ids)`.
- `promotions_all` — `forChannel`, `orderByDesc('id')`, `limit` 1–100 (default 20), `active()` when `active_only` is true or omitted.
- Empty id list / empty query → `[]`.
- Output: `PromotionResource::collection(...)->resolve()` (id, slug, title, description, status, dates, media, `products_count`).

`promotionsVersionStamp(): int` — `strtotime(Promotion::max('updated_at'))` or 0. Used as cache buster for page payloads and `promotions_all` widget caches.

`Promotion::forChannel` matches `channel_id` null **or** the current channel. `active()` is status active plus start/end window.

## `RandomProductWidgetResolver`

**Module called:** catalog-products (`ProductProjection`, `product_categories`).

`pickIds($config, $channelId, $locale)`:

- Returns `[]` if no channel.
- `limit` 1–48 (default 8).
- `in_stock_only` default true → `product_projections.in_stock`.
- Optional `category_id[]` via `whereExists` on `product_categories`.
- Projection filters: `channel_id`, `locale`, `user_group_id = 0`.
- `inRandomOrder()->limit()->pluck('product_id')`.

`refreshPagePayload` walks `payload['widgets'][slot][]` and re-picks IDs when `type === 'random_products'`.

`refreshConfig` re-picks on a single config array (not used by the API controllers).

## `TrendingProductWidgetResolver`

**Modules called:** catalog-products (`ProductProjection`, `product_categories`) and storefront analytics (`StorefrontEvent`).

`pickIds`:

- Returns `[]` if no channel.
- Config: `limit` 1–48 (default 8), `window_days` 1–90 (default 7), `metric` `clicks` \| `add_to_cart` \| `weighted` (default), `in_stock_only` default true, `category_id[]`.
- Cache key `trending:products:v1:{channel}:{locale}:{metric}:{window}:{limit}:{md5(categories)}:{inStock}` TTL **900s**.

Ranking from `storefront_events`: `consent = true`, `entity_type = product`, numeric `entity_id`, `occurred_at` in window.

| metric | Score |
|---|---|
| `clicks` | count of `click` |
| `add_to_cart` | count of `add_to_cart` |
| `weighted` | clicks + **5 ×** add_to_cart (`CART_WEIGHT`) |

Over-fetches events then keeps order while intersecting live projections (same stock/category filters as random). If fewer than `limit` ranked IDs, fills with newest `product_id` desc excluding already picked.

## `FooterSections` (`app/Support/FooterSections.php`)

Used by this module for **section keys/labels** only:

- `MenuController::resolveFooterSections()`
- Admin page form dropdown (`setting('footer.sections', ...)`)

`parse($raw)` accepts array, JSON array, or newline/comma-separated labels → `{ key, label, order }` with `key = Str::slug(label, '_')`.

Other methods (`normalizeColumnContentForApi`, contact line types, etc.) belong to **settings** (`SettingController`, `SettingResource`), not page/widget JSON.

## Config JSON written by admin (`buildConfig`)

Common (every type): `show_title`, `is_carousel`, `layout`, `section_label`, `images`, `images_links`, `images_alt`.

| type | Extra config keys |
|---|---|
| `featured_products` | `product_ids` |
| `random_products` | `limit`, `in_stock_only`, `category_id` |
| `trending_products` | `metric`, `window_days`, `limit`, `in_stock_only`, `category_id` |
| `products_by_category` | `category_id`, `products_by_category` (map category id → product ids) |
| `products_by_attribute` | `attribute_id`, `attribute_option_id`, `products` (map option id → product ids) |
| `category_list` | `category_id` |
| `attribute_options_list` | `attribute_id`, `attribute_option_id` |
| `carousel` | `carousel_transition`, `carousel_interval` (min 1000, default 5000), `overlay_opacity` 0–100, `carousel_loop`, `slides[]` (`image`, `alt`, `headline`, `subtitle`, `cta_text`, `cta_link`, `cta_color`, `cta_bg`, `text_position`) |
| `video` | `video_url`, `small_device_link`, `small_device_link_text`, `autoplay`, `muted` |
| `html` | `html`, `css`, `js`, `enable_preview` |
| `promotions_all` | `active_only`, `limit` (1–100, default 20) |
| `promotions_selected` | `promotion_ids` |
| `contact_form` | `show_phone`, `show_subject` (default true), plus copy keys listed in `contactFormTranslationFields()` |

Layouts allowed in admin: `1x1`, `1x4`, `1x6`, `2x3`, `3x3`, `1x6_tabbed`, `Xx4`, `carousel`.
