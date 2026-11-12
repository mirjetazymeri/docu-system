# Storefront API — Coupons and Promotions

Module index: [README.md](./README.md)

Shared middleware on `/api/v1/*`: `channel.api` (`ResolveApiChannel`), `locale.api` (`SetLocaleFromRequest`).

Channel resolution order (`ResolveApiChannel`): query `channel` (matched to `channels.code`), header `X-Channel` (same), authenticated user’s `currentChannel`, fallback `Channel` with `code = default`. Bound as `currentChannel` (may be null if none of those resolve).

Locale: query `locale` or header `X-Locale`, else `Accept-Language`, else channel default / `config('app.locale')`.

Promotions endpoints are unauthenticated.

---

## `GET /api/v1/promotions`

### Purpose

List campaigns for the current API channel.

### Query

| Param | Default | Description |
| ----- | ------- | ----------- |
| `active_only` | `1` | `FILTER_VALIDATE_BOOLEAN`. When true, `Promotion::active()`. |

### Response

`PromotionResource` collection:

```text
{ "data": [ Promotion, ... ] }
```

Each item (from `app/Http/Resources/Api/V1/PromotionResource.php`):

| Field | Source |
| ----- | ------ |
| `id` | model |
| `slug` | translation slug or default slug |
| `title` | translation title or default |
| `description` | translation or default |
| `status` | |
| `starts_at` / `ends_at` | ISO-8601 or null |
| `media.banner` / `icon` / `logo` | `PromotionService::mediaUrl` |
| `products_count` | `whenCounted('items')` (always loaded in this action) |

Cached 5 minutes (`promotions:list:...`).

---

## `GET /api/v1/promotions/{idOrSlug}`

### Purpose

Single campaign. `{idOrSlug}` is numeric id **or** default/translation slug (current + fallback locale).

### Query

Same `active_only` default as list (applies to resolve).

### Response

```text
{ "data": Promotion }
```

Same fields as list. 404 via `firstOrFail` if not in channel scope / not active when required.

Cached 5 minutes; key includes `updated_at` timestamp.

---

## `GET /api/v1/promotions/{idOrSlug}/products`

### Purpose

Products assigned to the campaign, ordered by `position`, `id`.

### Response

`PromotionProductResource` collection (`data` array). Per item (`app/Http/Resources/Api/V1/PromotionProductResource.php`):

| Field | Source |
| ----- | ------ |
| `id` | product id |
| `sku` | |
| `slug` / `name` | product translation for API locale |
| `position` | item |
| `pricing.price` | `resolvePricing` base (channel `product_prices.price`) |
| `pricing.final_price` | effective |
| `pricing.special_price` / `special_from` / `special_to` | |
| `image` | primary or first image `{ url: Storage::url(path), alt: name }` or null |

Missing product/translation/image fields may be null. Cached 5 minutes.

---

## `POST /api/v1/cart/coupon`

Controller owned by [cart-checkout](../cart-checkout/README.md); coupon rules owned here.

### Purpose

Apply a code to the current cart.

### Authentication

Optional `auth:sanctum`. Cart token: header `X-Cart-Token` or query `cart_token`.

### Request JSON

| Field | Rules |
| ----- | ----- |
| `code` | required, string, max 64 (`ApplyCouponRequest`) |

### Success

`CartResource`: includes `"coupon": "<coupon_code or null>"` plus cart totals/items. See cart module for full cart payload.

### Failures

`CartService::applyCoupon` / `CouponService`:

| Thrown message | When |
| -------------- | ---- |
| `Coupon code not found.` | no matching code for cart channel (or global null channel) |
| `Coupon is not active.` | status/window |
| `Coupon is not valid for this store.` | channel mismatch |
| `Coupon not available for guests.` | |
| `Coupon usage limit reached.` | |
| `You have already used this coupon.` | |
| `Order subtotal is below the minimum for this coupon.` | |
| `Order subtotal exceeds the maximum for this coupon.` | |

`applyCoupon` does **not** catch `RuntimeException`. Unlike `addItem` (explicit 422 JSON), mapping is Laravel default (`bootstrap/app.php` exceptions closure is empty).

---

## `DELETE /api/v1/cart/coupon`

Removes coupon, cancels `applied` usage, restores free-shipping original amount if stored, recalc totals. Success: `CartResource` with `coupon` null.

---

## CMS embedding (not this module’s routes)

`WidgetRenderer` adds `config.promotions` using `PromotionWidgetResolver` for widget types `promotions_all` and `promotions_selected`. Page/widget cache keys may include `promotionsVersionStamp()`. Documented in cms-pages-widgets.
