# Services — Coupons and Promotions

Module index: [README.md](./README.md)

---

## Class: `App\Services\Checkout\CouponService`

Source: `app/Services/Checkout/CouponService.php`

**Responsibility:** Locate coupons, validate against a cart, compute discount / free-shipping, persist apply/remove on cart + `coupon_usages`.

**Dependencies:** Eloquent `Cart`, `Coupon`, `CouponUsage`; `DB`; `CartService` via `app(CartService::class)` for `recalcTotals` after apply/remove. `Category` is imported and unused.

**Called by:** `CartService::applyCoupon`, `removeCoupon`, `refreshCouponEffects`, `mergeInto`; `TaxService::resolveEligibleDiscountItemIds`.

**Events / jobs:** none.

### `findByCode(string $code, ?int $channelId = null): ?Coupon`

Trims code. `whereRaw('LOWER(code) = ?', [mb_strtolower($code)])`. If `$channelId` set: `channel_id = $channelId` **or** `channel_id` null.

### `validateAndQuote(Cart $cart, Coupon $coupon): array`

Returns `[float $discount, bool $freeShipping]`. Throws `RuntimeException` with these messages:

| Condition | Message |
| --------- | ------- |
| `!isActiveNow()` | `Coupon is not active.` |
| cart and coupon both have channel_id and they differ | `Coupon is not valid for this store.` |
| guest cart and `!allow_guest` | `Coupon not available for guests.` |
| global usage_limit reached (`applied`+`consumed`) | `Coupon usage limit reached.` |
| per-user/email limit reached | `You have already used this coupon.` |
| subtotal (sum of item `row_total`) below min | `Order subtotal is below the minimum for this coupon.` |
| subtotal above max | `Order subtotal exceeds the maximum for this coupon.` |

Per-user limit: if `user_id` present, filter usages by `user_id`; else if cart `email`, filter by `email`; if guest without email, skip this check (“recheck at order submit” comment — no matching recheck in `CouponService` or `OrderService` was found).

Does **not** consult `userGroups` or `stackable`.

Discount:

- `by_percent`: `eligible * clamp(amount, 0..100) / 100`
- `by_fixed`: `min(amount, eligible)`
- `free_shipping`: discount `0`, `$freeShipping = true`
- then `max_discount_amount` cap if set
- `round($discount, 2)`

### `apply(Cart $cart, Coupon $coupon): Cart`

`DB::transaction`:

1. `validateAndQuote($cart->load('items'))`
2. If free shipping and shipping `amount` not empty: merge `orig_amount` + `amount = 0`, `shipping_total = 0`
3. Set `coupon_id`, `coupon_code`, `coupon_discount`, save
4. `CouponUsage::firstOrNew([coupon_id, cart_id])` fill user/email/amount/`applied`/ip/user_agent, save
5. `CartService::recalcTotals($cart->fresh('items'))`
6. Return `$cart->refresh()`

### `remove(Cart $cart): Cart`

Transaction: usages for this coupon+cart with status `applied` → `cancelled`. Restore `shipping.orig_amount` if present. Clear coupon fields, recalc totals.

### Eligibility

`eligibleSubtotal` / `eligibleCartItemIds` / `eligibleCartItemProductIds`:

- No products, no categories, and empty `conditions.attributes` → `null` (all items).
- Else union of: explicit product IDs; product IDs from `product_categories` for selected categories; product IDs matching **all** attribute conditions (intersect). Each attribute condition matches `product_attribute_values.value_integer` in option ids **or** `JSON_CONTAINS(value_json, ...)`.

`TaxService` uses `eligibleCartItemIds` so `OrderTotalsCalculator` only allocates coupon discount to those cart item IDs.

**Returns:** mutated `Cart` or quote tuple. **Exceptions:** `RuntimeException` as above.

---

## Class: `App\Services\Promotion\PromotionService`

Source: `app/Services/Promotion/PromotionService.php`

**Responsibility:** Sync translations and items, store/delete media, copy specials onto `product_prices`, rebuild projections, ping storefront revalidation.

**Dependencies (constructor):** `ProductPriceHistoryRecorder`.

**Also calls:** `Product`, `ProductPrice`, `PromotionItem`, `Storage` disk `public`, `ProductProjectionService` via `app()`, `StorefrontRevalidator::pingAfterCommit()`.

**Called by:** `Admin\PromotionController` store/update/delete.

**Constants:** `DISK = 'public'`, `DIR = 'promotions'`.

### `syncTranslations(Promotion $promotion, array $translations): void`

Skip empty locale. If title/slug/description all empty → delete that locale row. Else `updateOrCreate` by locale.

### `syncItems(Promotion $promotion, array $items): void`

For each row with `product_id > 0`:

- `price` always `null`
- `special_price` nullable decimal string
- `special_from` / `special_to` from row or promotion `starts_at`/`ends_at` date
- `position` = loop index + 1
- If `id` matches an existing item on this promotion, update; else `updateOrCreate` by `product_id`
- Then `applySpecialToProductPrices`

Deletes items not in `$keepIds`. Rebuilds projections for synced + removed product IDs.

**Transaction:** caller (`PromotionController`) wraps in `DB::transaction`.

### `processMedia(Request $request, Promotion $promotion, array $existing): array`

Fields `banner_image`, `icon`, `logo`. If `remove_{field}` boolean: delete file, null. Else if valid upload: delete old, `store('promotions', 'public')`. Else keep existing. Absolute `http(s)` paths are not deleted from disk.

### `deleteMediaFiles(Promotion $promotion): void`

Deletes the three files if they are local paths.

### `applySpecialToProductPrices(Promotion $promotion, PromotionItem $item): bool`

If item has no special fields, return false. Channel IDs: promotion’s `channel_id` if set, else all `product_prices.channel_id` for that product. For each existing `ProductPrice` row: update special fields only (not base price). If special or dates changed, insert `product_price_history` with `reason` = `'promotion:' . $promotion->id`, `changed_by` = `auth()->id()`.

### `rebuildProductProjections(array $productIds): void`

`ProductProjectionService::rebuild` per product; then `StorefrontRevalidator::pingAfterCommit()` (default tags from `config('external_catalog.revalidate_tags')`, fallback `['catalog']` — name only, no secrets).

### `mediaUrl(?string $path): ?string` (static)

Null → null. `http://` / `https://` unchanged. Else `asset('storage/' . ltrim(path))`. Used by `PromotionResource`.

---

## Class: `App\Services\Cms\PromotionWidgetResolver`

Source: `app/Services/Cms/PromotionWidgetResolver.php`

**Responsibility:** Resolve promotion cards for **CMS widget API output**. CMS owns widget types, config, and routes. This class only reads `Promotion` and maps `PromotionResource`.

**Called by:** `WidgetRenderer::appendPromotions` for types `promotions_all` and `promotions_selected`. Version stamp used by `Api\V1\WidgetController` and `Api\V1\PageController` cache keys.

### `resolveList(Widget $widget, ?string $locale = null): array`

- `promotions_selected`: `config.promotion_ids`, `forChannel`, **always** `active()`, preserve ID order.
- `promotions_all`: `active_only` default true if key missing; `limit` clamped 1–100 default 20; `forChannel`; `orderByDesc('id')`.
- Other types: empty.

Returns `PromotionResource::collection(...)->resolve()`.

### `promotionsVersionStamp(): int`

`(int) strtotime(max(promotions.updated_at))` or `0`.

---

## Adjacent services (owned elsewhere)

| Class | Role for this module |
| ----- | -------------------- |
| `CartService` | `applyCoupon` / `removeCoupon` wrap `CouponService`; `refreshCouponEffects` re-quotes or removes; merge copies coupon if target has none |
| `OrderService` | Copies `coupon_code` / `coupon_discount` onto order; sets matching usage to `consumed` + `order_id` (+ `used_at`, see [models.md](./models.md)) |
| `TaxService` | Passes `cart.coupon_discount` as `discountTotal`; line `discount_eligible` from `eligibleCartItemIds` |
| `ProductProjectionService` | Rebuild PLP/PDP after promotion item changes |
| `ProductPriceHistoryRecorder` | History rows when promotion specials change |
| `StorefrontRevalidator` | ISR ping after projection rebuild |
