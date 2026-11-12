# Validation — Coupons and Promotions

Module index: [README.md](./README.md)

All listed FormRequests have `authorize(): true`.

---

## Coupon traits

### `App\Http\Requests\Admin\Coupon\Concerns\NormalizesCouponConditions`

`prepareCouponFields()` (used by store/update/generate):

- Normalize `conditions.attributes` (resolve `attribute_code` → id, split string option ids).
- If attributes empty and `conditions_json` is filled, JSON-decode and normalize.
- Boolean-cast `allow_guest`, `stackable`.
- Unique int arrays for `category_ids`, `product_ids`, `user_group_ids`.

### `App\Http\Requests\Admin\Coupon\Concerns\ValidatesCouponScoping`

`after` validator:

- `category_ids` must all exist as `Category::forCurrentChannel()->visibleInCatalog()`.
- `product_ids` must all exist as `Product::forCurrentChannel()`.
- Attribute ids must be `Attribute::forCurrentChannel()`.
- Each `option_ids` value must belong to that row’s `attribute_id` (`attribute_options` map).

---

## `StoreCouponRequest`

Source: `app/Http/Requests/Admin/Coupon/StoreCouponRequest.php`

Used when `CouponController::store` is **not** bulk (manually `validateResolved()`).

| Field | Rules |
| ----- | ----- |
| `code` | required, string, max 64, unique `coupons,code` |
| `name` | nullable, string, max 191 |
| `description` | nullable, string |
| `type` | required, in `by_percent`, `by_fixed`, `free_shipping` |
| `amount` | required, numeric, min 0 |
| `status` | required, in `active`, `inactive` |
| `usage_limit` | nullable, integer, min 1 |
| `usage_limit_per_user` | nullable, integer, min 1 |
| `starts_at` | nullable, date |
| `ends_at` | nullable, date, after_or_equal `starts_at` |
| `min_subtotal` / `max_subtotal` / `max_discount_amount` | nullable, numeric, min 0 |
| `allow_guest` / `stackable` | sometimes, boolean |
| `conditions` | nullable, array |
| `conditions.attributes.*` | `attribute_id` required integer exists `attributes`; `option_ids` required array min 1; each exists `attribute_options` |
| `user_group_ids.*` | integer exists `user_groups` |
| `product_ids.*` | integer exists `products` |
| `category_ids.*` | integer exists `categories` |

`amount` is required even for `free_shipping` (no type-conditional rule).

---

## `UpdateCouponRequest`

Extends `StoreCouponRequest`. Overrides `code` unique to `Rule::unique('coupons','code')->ignore($this->route('id'))`.

Used as a type-hint on `CouponController::update`.

---

## `GenerateCouponsRequest`

Source: `app/Http/Requests/Admin/Coupon/GenerateCouponsRequest.php`

Used from `store()` when `creation_mode === 'bulk'` (and from unrouted `generate()`).

Same discount/scoping fields as store **except no `code`**. Extra:

| Field | Rules |
| ----- | ----- |
| `quantity` | required, integer, min 1, max 1000 |
| `prefix` | nullable, string, max 20 |
| `length` | required, integer, min 4, max 32 |
| `charset` | required, string, min 2 |

`creation_mode` is **not** a validated field; the controller reads it from the raw request.

---

## `StorePromotionRequest` / `UpdatePromotionRequest`

Sources: `app/Http/Requests/Admin/Promotion/StorePromotionRequest.php`, `UpdatePromotionRequest.php`

| Field | Rules |
| ----- | ----- |
| `title` | required, string, max 255 |
| `slug` | nullable, string, max 255, unique `promotions.slug` (update ignores route `id`) |
| `description` | nullable, string |
| `status` | required, in `active`, `inactive` |
| `starts_at` / `ends_at` | nullable date; ends after_or_equal starts |
| `banner_image` | nullable, image, max 5120 (KB) |
| `icon` / `logo` | nullable, image, max 2048 (KB) |
| `remove_banner_image` / `remove_icon` / `remove_logo` | nullable, boolean |
| `translations` | nullable array |
| `translations.*.locale` | required_with translations, string, max 10 |
| `translations.*.title` / `slug` | nullable string max 255 |
| `translations.*.description` | nullable string |
| `items` | nullable array |
| `items.*.id` | nullable integer |
| `items.*.product_id` | required_with items, integer, exists `products` |
| `items.*.special_price` | nullable numeric min 0 |
| `items.*.special_from` / `special_to` | nullable date |

`after` validator:

- Slug uniqueness is checked again on `Str::slug(slug ?: title)` (store: any existing; update: other ids). Empty slug after that check returns early (model hook still generates a slug on save).
- `items.*.product_id` values must belong to `Product::forCurrentChannel()`.

Translation slugs are **not** uniquely validated.

---

## `ApplyCouponRequest`

Source: `app/Http/Requests/Api/V1/Cart/ApplyCouponRequest.php`

Owned with cart API; validates coupon apply body.

| Field | Rules |
| ----- | ----- |
| `code` | required, string, max 64 |

No existence check here; `CartService::applyCoupon` throws `Coupon code not found.` if `findByCode` misses.
