# Coupons and Promotions

## Purpose

Admin management of coupon codes (checkout discounts / free shipping) and promotional campaigns (storefront listing, media, and product special prices). Storefront listing is public API; coupon redemption runs through cart checkout.

## Responsibilities

This module owns:

- Coupon CRUD, tracking stats, and bulk code generation in admin
- Promotion campaign CRUD, translations, media, and product line items in admin
- Coupon lookup, quote, apply, and remove logic (`CouponService`)
- Pushing promotion special prices onto `product_prices` and rebuilding catalog projections (`PromotionService`)
- Public Storefront API for promotion list, detail, and products

This module does **not** own:

- CMS widgets (`promotions_all`, `promotions_selected`) — CMS owns widget CRUD, placement, and API. `PromotionWidgetResolver` is a **consumer** of promotion records. See [cms-pages-widgets](../cms-pages-widgets/README.md).
- Cart persistence, totals, and order placement — [cart-checkout](../cart-checkout/README.md)
- Catalog product/price schema — [catalog-products](../catalog-products/README.md)
- Channel/locale resolution — [channels-locales](../channels-locales/README.md)

## Source Code Locations

```text
app/Models/Coupon.php
app/Models/CouponUsage.php
app/Models/Promotion.php
app/Models/PromotionItem.php
app/Models/PromotionTranslation.php
app/Http/Controllers/Admin/CouponController.php
app/Http/Controllers/Admin/PromotionController.php
app/Http/Controllers/Api/V1/PromotionController.php
app/Http/Resources/Api/V1/PromotionResource.php
app/Http/Resources/Api/V1/PromotionProductResource.php
app/Services/Checkout/CouponService.php
app/Services/Promotion/PromotionService.php
app/Services/Cms/PromotionWidgetResolver.php
app/Http/Requests/Admin/Coupon/
app/Http/Requests/Admin/Promotion/
app/Http/Requests/Api/V1/Cart/ApplyCouponRequest.php
resources/views/admin/coupons/
resources/views/admin/promotions/
database/migrations/2025_08_29_195534_create_coupons_table.php.php
database/migrations/2025_08_29_195618_create_coupon_usages_table.php.php
database/migrations/2025_08_29_195759_add_coupon_to_carts_table.php.php
database/migrations/2026_06_05_120000_add_channel_id_to_coupons_table.php
database/migrations/2026_05_26_100000_create_promotions_tables.php
routes/web.php          (admin coupons*, promotions*)
routes/api.php          (promotions*, cart/coupon)
```

Related but owned elsewhere:

```text
app/Http/Controllers/Api/V1/CartController.php   (applyCoupon / removeCoupon)
app/Services/Checkout/CartService.php            (delegates to CouponService)
app/Services/Checkout/OrderService.php           (consumes coupon usage on place-order)
app/Services/Tax/TaxService.php                  (line discount eligibility via CouponService)
app/Http/Controllers/Admin/LookupController.php  (lookups/promotions for CMS widget picker)
app/Services/Cms/WidgetRenderer.php              (appends promotions into widget config)
```

## Main Components

| Type | Class | Responsibility |
| ---- | ----- | -------------- |
| Model | `App\Models\Coupon` | Coupon code, discount rules, channel, scoping relations |
| Model | `App\Models\CouponUsage` | Per-cart apply / consume / cancel record |
| Model | `App\Models\Promotion` | Campaign, media paths, channel, active window |
| Model | `App\Models\PromotionItem` | Product assignment and promotion special-price override |
| Model | `App\Models\PromotionTranslation` | Locale title/slug/description |
| Controller | `App\Http\Controllers\Admin\CouponController` | Admin coupons UI, DataTables, bulk generate |
| Controller | `App\Http\Controllers\Admin\PromotionController` | Admin campaigns CRUD, product-pricing JSON |
| Controller | `App\Http\Controllers\Api\V1\PromotionController` | Public promotion list/show/products |
| Resource | `App\Http\Resources\Api\V1\PromotionResource` | Translated campaign payload + media URLs |
| Resource | `App\Http\Resources\Api\V1\PromotionProductResource` | Product card fields + resolved pricing |
| Service | `App\Services\Checkout\CouponService` | Find, quote, apply, remove, eligibility |
| Service | `App\Services\Promotion\PromotionService` | Translations, items, media, special prices, projections |
| Resolver | `App\Services\Cms\PromotionWidgetResolver` | CMS widget consumer of promotions |
| FormRequest | `StoreCouponRequest`, `UpdateCouponRequest`, `GenerateCouponsRequest` | Admin coupon validation |
| FormRequest | `StorePromotionRequest`, `UpdatePromotionRequest` | Admin promotion validation |
| FormRequest | `ApplyCouponRequest` | API `code` for cart coupon apply |

No repositories, actions, jobs, events, listeners, policies, or Artisan commands for this module were found.

## Module Dependencies

```text
This module depends on:
- catalog-products
- cart-checkout
- channels-locales

This module is used by:
- cart-checkout (apply/remove/refresh coupon; copy coupon onto order)
- tax (CouponService::eligibleCartItemIds for line discounts)
- cms-pages-widgets (PromotionWidgetResolver, widget/page cache stamp)
- catalog-products (product_prices special fields + projections rebuilt from promotions)
- orders (orders.coupon_code / coupon_discount; coupon_usages.order_id written at place-order)
- external-catalog (StorefrontRevalidator::pingAfterCommit after promotion item sync/delete)
```

Verified import/call sites are listed in [dependencies.md](./dependencies.md).

## Entry Points

| Entry | Path | Owner |
| ----- | ---- | ----- |
| Admin web | `/admin/coupons*`, `/admin/promotions*` | this module |
| Admin lookup | `GET /admin/lookups/promotions` | CMS lookup controller; reads `Promotion` |
| Storefront API | `GET /api/v1/promotions*` | this module |
| Cart API | `POST/DELETE /api/v1/cart/coupon` | cart-checkout controller; this module’s `CouponService` |
| CMS widgets | `GET /api/v1/widgets*` | cms-pages-widgets; resolver in this module |

No scheduled tasks or queue jobs for coupons/promotions were found.

## Important Business Rules

Proven by code:

- Admin coupon and promotion writes require `auth()->user()->current_channel_id`; missing channel aborts `403` (`No channel selected.`).
- Coupon codes are globally unique (`coupons.code` unique index). Lookup at apply time is case-insensitive (`LOWER(code)`).
- One coupon per cart: `CouponService::apply()` overwrites `cart.coupon_id` / `coupon_code` / `coupon_discount`. `stackable` is stored; migration comment and admin UI mark it as future-use. `CouponService` does not read `stackable`.
- Coupon types: `by_percent`, `by_fixed`, `free_shipping`. Percent is clamped to 0–100. Fixed is `min(amount, eligible subtotal)`. Free shipping zeros current shipping amount and stashes `orig_amount`.
- Usage counts toward limits when `coupon_usages.status` is `applied` or `consumed`.
- Guest apply is blocked when `allow_guest` is false. Per-user limit for guests without email is deferred (“recheck at order submit” comment); `CouponService` does not re-check at order submit itself — `OrderService` only marks usage consumed.
- `user_group_ids` are synced on save. `CouponService` does **not** check `userGroups` when quoting or applying.
- Product / category / attribute scopes union into an eligible product set. Attribute conditions are AND across attributes. Empty scope means all cart items are eligible.
- Promotions API `active_only` defaults to true (`FILTER_VALIDATE_BOOLEAN` on query, default `'1'`).
- API `forChannel($channelId)` includes rows with `channel_id` null **or** matching channel. Admin `forCurrentChannel()` is exact `channel_id` match only.
- Saving a promotion item with any special field set overwrites `product_prices.special_price` / `special_from` / `special_to` for the promotion’s channel(s). Base `price` is not changed.
- Bulk coupon HTTP: only `GET admin/coupons/generate` is routed; it redirects to create with `mode=bulk`. Generation runs on `POST admin/coupons` when `creation_mode=bulk`. See [workflows.md](./workflows.md).

## Documents

- [models.md](./models.md)
- [database.md](./database.md)
- [controllers.md](./controllers.md)
- [services.md](./services.md)
- [requests-validation.md](./requests-validation.md)
- [routes.md](./routes.md)
- [api.md](./api.md)
- [workflows.md](./workflows.md)
- [dependencies.md](./dependencies.md)
- [tests.md](./tests.md)
