# Sales specialist — architecture

## Surfaces

```
Browser (Blade layouts.master)
  ├── GET  /sales-specialist/dashboard
  ├── GET  /sales-specialist/orders
  ├── GET  /sales-specialist/orders/create   ← JS also calls /api/v1/* (catalog + geo)
  ├── POST /sales-specialist/orders
  ├── GET  /sales-specialist/orders/{order}
  ├── GET  /sales-specialist/customers/search
  ├── POST /sales-specialist/customers
  ├── POST /sales-specialist/orders/cart-preview
  └── POST /sales-specialist/orders/fulfillment-options
```

All of the above sit on `routes/web.php` with:

`auth` → `verified.if-required` → `set.user.locale` → `role:Sales Specialist`

The `web` group also runs `SetCurrentChannel` (`bootstrap/app.php`).

There are **no** `/api/v1/sales-specialist` routes.

## Layers

```
Blade (resources/views/sales-specialist)
        │
        ▼
SalesSpecialist\*Controller
        │
        ├── CustomerController → User / Spatie Role / UserGroup
        └── OrderController
                ├── ResolvesSalesChannel (trait)
                └── SalesSpecialistOrderService
                        ├── CartService          (cart-checkout)
                        ├── OrderService::place  (cart-checkout)
                        ├── PaymentDriverManager (payments)
                        └── TaxService           (tax)
```

No jobs, observers, policies, or FormRequest authorize checks unique to this module (`authorize()` is `return true`; the route middleware is the gate).

## Channel binding

Specialist HTTP is **not** behind `channel.api`. Channel comes from the authenticated user.

| Step | Who | Behavior |
|---|---|---|
| Every web request | `SetCurrentChannel` | If `current_channel_id` empty, set from `$user->channels()->first()`. If current id is not in the pivot, fall back. Then `app()->instance('currentChannel', $user->currentChannel)` (may be `null`). |
| Order HTTP | `ResolvesSalesChannel::resolveChannelId()` | Prefer bound `currentChannel->id`. Else `user.current_channel_id`. Else `$user->channels()->first()` **or** `Channel::query()->orderBy('id')->first()`. Missing any channel → HTTP **422** `No sales channel is configured. Contact an administrator.` If the user had no `current_channel_id`, persist it and `channels()->syncWithoutDetaching`. |
| Place / preview | `SalesSpecialistOrderService::ensureChannelBound()` | Same fallbacks; missing channel → `RuntimeException` `No channel available.` |

Header dropdowns in `resources/views/layouts/components/main-header1.blade.php` link to `admin.channels.switch` and `admin.locale.switch`. Those routes live in the **Admin** group (`role:Admin` plus `channel_view` / locale switch). A Sales Specialist clicking them is rejected by that middleware. Channel for this desk is therefore the user’s `current_channel_id` / pivot assignment (admin-managed), plus the fallback to the first `channels` row if none is assigned.

Catalog and geo AJAX on the create form send `X-Channel: {currentChannel.code}` (JS fallback `'default'`) to `/api/v1/...`, which is resolved by `channel.api` — a **second** channel resolution path, independent of `ResolvesSalesChannel`.

## Scratch cart

`prepareCart()` does not use `CartService::getOrCreate()`.

1. Look up `carts` where `channel_id` = bound channel, `session_token` = `'sales-specialist:' . Auth::id()`, `status` = `active`.
2. If found: delete items, delete `payment()` row, zero totals, null user/email/addresses, save. **Not** cleared: `coupon_id`, `coupon_code`, `deposit_total`, `meta`, `currency`.
3. If missing: `Cart::create` with that token, `currency` **`EUR`**, `status` `active`.
4. `CartService::setCustomer`, then `addItem` per line (product must be `forChannel` + `status=active`), then optional `setAddresses`.

`previewCart` may then `setShippingMethod` and always `recalcTotals`. `placeForCustomer` always `setShippingMethod` + `setPaymentMethod` then `OrderService::place()`.

After a successful place, checkout sets the cart to `ordered`. The next preview/place does not reuse that row (`status` filter) and creates a new scratch cart.

## Source and specialist columns

`OrderService::place()`:

```
'sales_specialist_id' => $opts['sales_specialist_id'] ?? null,
'source'              => $opts['source'] ?? null,
```

| Caller | `source` | `sales_specialist_id` |
|---|---|---|
| `SalesSpecialistOrderService::placeForCustomer()` | `Order::SOURCE_SALES_SPECIALIST` (`sales_specialist`) | `Auth::id()` |
| `CheckoutController::placeOrder()` | omitted → `null` | omitted → `null` |
| `FinalizeRaiAcceptOrderJob` / `FinalizePcbBankOrderJob` | omitted → `null` | omitted → `null` |

Constants `SOURCE_STOREFRONT`, `SOURCE_API`, and `SOURCE_ADMIN` exist on `Order` and are **not** assigned by these callers. Null source labels as “Online” (`Order::sourceLabel()`).

Idempotency key from this module is `'sales-specialist:' . Auth::id() . ':' . Str::uuid()` — unique per submit, so it does not debounce double-clicks across requests.

## Customer modes

| Mode | Cart / order user | Email |
|---|---|---|
| `existing` | `User` with role `Customer` (`findOrFail` after validation) | That user’s `email` |
| `guest` | `user_id` null | `guest_email` or `billing.email` (first non-empty). Blocked if `CheckoutAddressFields::guestCheckoutEnabled()` is false. |

Guest checkout flag: setting `checkout.guest_enabled`, else `system.checkout`, else config default (true if unset). Same helper as storefront checkout.

Creating a **New Customer** always creates a `User` (not a guest order). Guest mode does not create an account.

## Create-order UI vs unused controller methods

The create Blade (`_create_scripts.blade.php`) loads:

| UI need | Actual HTTP |
|---|---|
| Customer search / create | `sales-specialist.customers.search` / `.store` |
| Live totals | `POST sales-specialist.orders.cart-preview` |
| Shipping + payment pickers | `POST sales-specialist.orders.fulfillment-options` |
| Product picker | **Public** `GET /api/v1/products/search` (≥2 chars) or `GET /api/v1/products-projection` — **not** `sales-specialist.products.search` |
| Countries / cities | **Public** `GET /api/v1/countries` and `GET /api/v1/countries/{code}/cities` |

`OrderController::searchProducts` **is** routed (`sales-specialist.products.search`) but unused by this Blade.

`searchShippingMethods` and `searchPaymentMethods` exist on the controller and have **no** routes.

`OrderController::create(PaymentDriverManager $paymentDrivers)` type-hints the manager and does not use it.

## What this desk does not do

- Edit, cancel, refund, ship, invoice, or assign shipments (no matching routes).
- Apply coupons, rental lines, or customization uploads (create form only sends `product_id` + `qty`).
- Place redirect-gateway orders (those drivers are filtered out of fulfillment preview).
- See other specialists’ orders, or all storefront orders.

Admin dashboard `SalesStatisticsService` reads `sales_specialist_id` for reporting; that is admin, not this module.
