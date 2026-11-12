# Architecture

## Layers

```
Admin (role:Admin + page_* / widget_* permissions)
  PageController / WidgetController
    Page, Widget, PageWidget, translations, channel pivots
         │
         ▼
Public API  (channel.api + locale.api)
  PageController.show
  WidgetController.show | byPlacement | home
  MenuController.index | show
         │
         ▼
WidgetRenderer
  ├── PromotionWidgetResolver      (promotions_all, promotions_selected)
  ├── RandomProductWidgetResolver  (random_products)
  └── TrendingProductWidgetResolver (trending_products)
```

Admin never calls the resolvers. Resolvers run only when building storefront JSON.

## Page tree

- `pages.parent_id` is a self-FK (`nullOnDelete`).
- Nested URL is `Page::path`: ancestor slugs joined with `/` (root first).
- Public `GET /api/v1/pages/{slugOrPath}` walks path segments: each segment is `slug` under the previous page’s id as `parent_id`. A numeric `{slugOrPath}` is treated as page id.
- Sibling reorder in admin is `POST /admin/pages/reorder` (`parent_id` + `ordering[]`), scoped to the current channel.

Cycle guards: `UpdatePageRequest` and `Page::saving` reject self-parent and parent-is-descendant.

## Content overlay (API page show)

`Api\V1\PageController::show` mutates the in-memory page then wraps `PageResource`:

1. Locale row via `Page::translation($locale)` (requested locale, else `config('app.fallback_locale')`).
2. If a translation exists, title/content/meta/nav_label are replaced when the translation field is non-empty (`?:`).
3. Matching `page_channel_contents` row: title/content/meta assigned with `??` (null keeps previous).

`PageResource` then sets **title** and **meta** from the translation again (`translation?->title ?: $this->title`). Channel overrides on title/meta can therefore be replaced by the translation in the JSON. **content** in the resource is `$this->content` (already overlaid in the controller).

## Widget placement vs home list

| Surface | Source | Channel | Publish |
|---|---|---|---|
| Page payload `widgets` | `page_widgets` for that page | Page must be in channel; widget must include channel if it has any channels | Page must be published |
| `GET widgets?page={slug}` | `page_widgets` for slug in channel | Page `forChannel`; widget channels **not** filtered | **Not** required |
| `GET widgets/home` | `widgets` rows | **Not** filtered by channel | N/A (`status=active`, `hide_on_home=false`) |
| `GET widgets/{id}` | single widget | 404 if widget has channels and current channel is missing | Inactive widgets are still returned |

Placement filters (page show and byPlacement): `active`, `starts_at` / `ends_at` window, optional `visibility.devices` vs `?device=` (default `desktop`). Migration comment mentions `groups` in `visibility`; **no code reads `groups`**. `$groupId` from Sanctum is only part of the page cache key.

Admin attach sets `slot` (default `content`), next `position`, `active=true`. It does not set `visibility`, `starts_at`, or `ends_at` (columns exist; window is shown read-only on the page edit UI).

## Widget resolve pipeline

`WidgetRenderer::resolve($widget, ['locale' => ...])`:

1. `localizedConfig($locale)` — `array_replace_recursive` of widget `config` and translation `config`.
2. Expand `slides[].image` and `images[]` to `asset('storage/...')`.
3. Type appenders (no-ops for other types).

`widget_key` is `{type}.{layout}` plus `.carousel` when `config.is_carousel` is truthy. Default layout `1x1`.

Product-heavy types (`featured_products`, `products_by_*`, lists) leave IDs in config. The storefront is expected to load catalog via products-projection (see [dependencies.md](dependencies.md)).

## Caching

TTL is **5 minutes** (`Cache::remember`). There is **no** `Cache::forget` for these keys on admin save.

| Key pattern | Used by | Versioning |
|---|---|---|
| `page:{id}:{updated_at}:{channel}:{locale}:{groupId}:{device}:{slot}:{promoStamp}` | Page show | page `updated_at` (touched on widget attach/detach/reorder/toggle); `PromotionWidgetResolver::promotionsVersionStamp()` |
| `widget:{id}:{updated_at}:{locale}:{promoStamp}` | Widget show (not `random_products`) | widget `updated_at`; promo stamp only when type is `promotions_all` |
| `widget:placement:{page_widget_id}:{widget.updated_at}:{locale}:{promoStamp}` | byPlacement | same |
| `widget:home:{id}:{updated_at}:{locale}:{promoStamp}` | home | same |
| `menu:{location}:channel:{id}:locale:{locale}` | menus | **no** page `updated_at` — nav changes can stay stale until TTL |

`random_products` skips widget cache. After a cached **page** payload is loaded, `RandomProductWidgetResolver::refreshPagePayload()` re-picks `product_ids` so random sets are not frozen.

`trending_products` ranking is cached separately: `trending:products:v1:...` for **900 seconds**.

`promotionsVersionStamp()` is `strtotime(max(promotions.updated_at))` (0 if empty). Widget show uses it only for `promotions_all`; page cache always includes it.

## Footer sections

`footer.sections` setting (newline/comma/JSON list) is parsed by `App\Support\FooterSections`. Page admin offers those labels as `footer_section`. Menu API groups footer items by section key (`Str::slug(label, '_')`), inherits parent section, default `'General'`.

Footer **column content** (phones, social URLs) is settings + `SettingResource`, not the page/widget APIs.

## Unused / stale pieces

- `config/pagebuilder.php` — never referenced (`config('pagebuilder')` is unused). Types there (`text`, `image`, `product_grid`, `banner`, …) are **not** admin widget types.
- `database/seeders/PageSeeder.php` — not called from `DatabaseSeeder`; references missing `PageSection` / `PageSectionTranslation`, `pages.extra`, and `page_translations.slug`.
- Link tables `widget_products` / `widget_categories` / `widget_attributes` were dropped; all type data is in `widgets.config` JSON.
- `App\Http\Resources\Api\V1\WidgetResource` exists but API widget controllers build arrays inline.
