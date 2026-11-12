# Controllers — Coupons and Promotions

Module index: [README.md](./README.md)

Admin group: `auth`, `verified.if-required`, `set.user.locale`, `role:Admin`, prefix `admin`, name prefix `admin.` (`routes/web.php`).

Additional permission middleware is applied in each controller constructor via `check.permission:{name}` (`App\Http\Middleware\AutoAuthorizePermission`). Super admin bypasses; otherwise `hasAccessLevelPermission`. Missing permission aborts 403.

Permissions are seeded in `database/seeders/ModulePermissionSeeder.php` as `coupon_{view,create,edit,delete}` and `promotion_{view,create,edit,delete}`.

---

## `App\Http\Controllers\Admin\CouponController`

Source: `app/Http/Controllers/Admin/CouponController.php`

### Responsibility

Admin coupon tracking index (DataTables + stats), create/edit/delete, and bulk generation through `store()`.

### Dependencies

None injected. Uses `Coupon`, `CouponUsage`, `UserGroup`, `Product`, `Category`, `Attribute`, FormRequests, DataTables, DB.

### Permissions

| Methods | Permission |
| ------- | ---------- |
| `index` | `coupon_view` |
| `create`, `store`, `generateForm`, `generate` | `coupon_create` |
| `edit`, `update` | `coupon_edit` |
| `delete` | `coupon_delete` |

### Methods

#### `index(Request $request)`

- Route: `GET admin/coupons` (`admin.coupons.index`)
- Non-AJAX: view `admin.coupons.track` with channel-scoped stats (`total`, `active`, redemptions and discount sum from usages with status `applied`/`consumed`), batch aggregations, last 50 usages.
- AJAX: Yajra DataTables on `Coupon::forCurrentChannel()`, optional filters `q` (code/name), `status`, `batch`. Columns include usage counts for `applied`/`consumed`.

#### `create()`

- Route: `GET admin/coupons/create`
- View `admin.coupons.create` plus `formExtras()` (user groups, catalog-visible categories for current channel, attributes/options, empty selections).
- Query `mode=bulk` opens the bulk tab (`creation_mode`).

#### `store(Request $request)`

- Route: `POST admin/coupons`
- If `creation_mode === 'bulk'`: builds `GenerateCouponsRequest` from the request, `validateResolved()`, then `generateBulk()`.
- Else: same pattern with `StoreCouponRequest`. Transaction: `Coupon::create(validated + channel_id)`, sync `userGroups` / `products` / `categories`.
- Redirect `admin.coupons.index` with flash `Coupon created.` (bulk: success with quantity and batch, filtered by `batch`).

`store()` is type-hinted as generic `Request` so both FormRequests can be applied manually.

#### `edit(int $id)` / `update(UpdateCouponRequest $req, int $id)`

- Channel-scoped `findOrFail`.
- Update transaction: update validated + `channel_id`, resync pivots.
- Redirect back with `Coupon updated.`

#### `delete(int $id)`

- Channel-scoped delete (usages cascade via FK).
- Redirect index with `Coupon deleted.`

#### `generateForm()`

- Route: `GET admin/coupons/generate` (`admin.coupons.generate.form`)
- Marked `@deprecated Use create page with bulk tab`
- **Only HTTP route for “generate”.** Implementation: `redirect()->route('admin.coupons.create', ['mode' => 'bulk'])`.
- There is **no** `POST admin/coupons/generate` route.

#### `generate(GenerateCouponsRequest $req)`

- Marked `@deprecated Bulk creation goes through store()`
- Calls `generateBulk()`. **Not registered in `routes/web.php`.** Permission middleware still lists it.

#### `generateBulk(GenerateCouponsRequest $req)` (private)

See [workflows.md](./workflows.md). Creates `quantity` unique codes (`prefix` + random `length` from `charset`), `batch = 'B' + Str::upper(Str::random(6))`, same payload/pivots, `channel_id` current. Unique check is global `Coupon::where('code')`. Max attempts `quantity * 20`; failure returns validation error on `quantity`.

`formExtras()` / `currentChannelId()`: abort 403 if no current channel. Product prefill from coupon products. Product search in the form uses `admin.products.search` (catalog).

---

## `App\Http\Controllers\Admin\PromotionController`

Source: `app/Http/Controllers/Admin/PromotionController.php`

### Responsibility

Admin campaign CRUD and a JSON helper for product channel prices on the form.

### Dependencies

Constructor: `PromotionService`.

### Permissions

| Methods | Permission |
| ------- | ---------- |
| `index`, `productPricing` | `promotion_view` |
| `create`, `store` | `promotion_create` |
| `edit`, `update` | `promotion_edit` |
| `delete` | `promotion_delete` |

### Methods

#### `index(Request $request)`

- `GET admin/promotions`
- AJAX DataTables: `Promotion::forCurrentChannel()->with('channel')->withCount('items')`. Channel column shows channel name or translation key `all_channels` when `channel` is null.
- View `admin.promotions.index`.

#### `create()` / `store(StorePromotionRequest $request)`

Transaction: `Promotion::create` of `title`, `slug`, `description`, `status`, `starts_at`, `ends_at` plus `channel_id` current; `processMedia` (new upload, existing `[]`); `syncTranslations`; `syncItems`. Redirect index `Promotion created.`

Forms use `enctype="multipart/form-data"`.

#### `edit` / `update(UpdatePromotionRequest $request, int $id)`

Loads translations, items.product.translations, items.product.prices. Update same columns + media (existing banner/icon/logo), translations, items. Redirect to **edit** with `Promotion updated.`

#### `productPricing(Request $request)`

- `GET admin/promotions/product-pricing` (`admin.promotions.product_pricing`)
- Query: `product_id` required (`<= 0` → 404). `channel_id` query or current user channel.
- Returns JSON: `channel_id`, `price`, `special_price`, `special_from`/`special_to` as `Y-m-d`, `currency` (default `'EUR'` if no row).
- Used by `resources/views/admin/promotions/_form.blade.php`.

#### `delete(int $id)`

Transaction: load items, `deleteMediaFiles`, delete promotion (items/translations cascade), `rebuildProductProjections` for those product IDs. Redirect index `Promotion deleted.`

`columns()` does not include media; media is applied in a second `update` from `processMedia`.

---

## `App\Http\Controllers\Api\V1\PromotionController`

Source: `app/Http/Controllers/Api/V1/PromotionController.php`

### Responsibility

Public storefront listing of campaigns and their products. Cached 5 minutes.

### Dependencies

None injected. Uses `Promotion`, `PromotionResource`, `PromotionProductResource`, `Cache`. Group middleware: `channel.api`, `locale.api` (`routes/api.php` prefix `v1` → `/api/v1`).

No Sanctum. No permission middleware.

### Methods

#### `index(Request $request)`

- `GET /api/v1/promotions`
- Channel: `app('currentChannel')?->id`. Locale: `app()->getLocale()`.
- Query `active_only` default `'1'` via `FILTER_VALIDATE_BOOLEAN`.
- Cache key `promotions:list:{channelId|all}:{locale}:{0|1}`.
- Query: translations, `withCount('items')`, `forChannel`, `orderByDesc('id')`, `active()` if active_only.
- JSON `{ data: PromotionResource collection }`.

#### `show(Request $request, string $idOrSlug)`

Resolves promotion then caches `promotions:show:{id}:{updated_at timestamp}:{channel}:{locale}` wrapping `PromotionResource->toArray()`. JSON `{ data: ... }`.

#### `products(Request $request, string $idOrSlug)`

Items with product translations (current + fallback locale), images, prices (filtered to channel when channel id present). Order `position`, `id`. Cache key `promotions:products:{id}:{updated_at}:{channel}:{locale}`. Returns `PromotionProductResource::collection` (Laravel `data` wrapper).

#### `resolvePromotion` (private)

`forChannel` + optional `active()`. Numeric `idOrSlug` → `whereKey`. Else `slug` on promotion **or** translation slug in current/fallback locales. `firstOrFail` → 404.

No cache invalidation on admin save was found; TTL is 5 minutes. Widget `promotions_all` uses `PromotionWidgetResolver::promotionsVersionStamp()` (max `promotions.updated_at`) as a cache-key suffix. `promotions_selected` stamp is `0` in `Api\V1\WidgetController`.

---

## Related controllers (not owned here)

### `App\Http\Controllers\Api\V1\CartController`

`applyCoupon(ApplyCouponRequest)` / `removeCoupon(Request)`: resolve cart (`X-Cart-Token` or `cart_token`, optional Sanctum user), then `CartService::applyCoupon` / `removeCoupon`. Returns `CartResource`. Unlike `addItem`, **these methods do not catch** `RuntimeException`. `bootstrap/app.php` `withExceptions` is empty — HTTP mapping of coupon quote failures is Laravel default, not customized in this repo.

`clear()` force-fills `coupon_id`, `coupon_code`, `coupon_discount` to empty.

### `App\Http\Controllers\Admin\LookupController::promotions`

`GET admin/lookups/promotions`, permission `lookup_view`. Select2-style `{ results, pagination.more }` for CMS `promotions_selected` widget (`resources/views/admin/widgets/partials/_scripts.blade.php`). Query `Promotion::forCurrentChannel()`.
