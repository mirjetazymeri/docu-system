# Sales specialist — dependencies

Verified from constructors, imports, queries, middleware, and the create-order Blade. This module does not own the dependency modules’ tables or admin UIs.

Sibling folders: [`../auth/README.md`](../auth/README.md), [`../users-access-control/README.md`](../users-access-control/README.md), [`../catalog-products/README.md`](../catalog-products/README.md), [`../cart-checkout/README.md`](../cart-checkout/README.md), [`../orders/README.md`](../orders/README.md), [`../channels-locales/README.md`](../channels-locales/README.md), [`../payments/README.md`](../payments/README.md), [`../shipping/README.md`](../shipping/README.md), [`../tax/README.md`](../tax/README.md).

## auth

| Touch point | How |
|---|---|
| Route middleware | `auth`, `verified.if-required` (`EnsureEmailIsVerifiedIfRequired`) |
| Fortify | `LoginResponse` redirects Sales Specialist to `sales-specialist.dashboard` |
| CSRF | Web middleware; JS `X-CSRF-TOKEN` on JSON posts |
| `UserObserver` | Customer `User::create` → `MailService::sendWelcome` + `notifyAdminNewUser` after commit |
| Password | New customers get `str()->random(32)` hashed by `User` cast; `email_verified_at` set so verification is skipped |

## users-access-control

| Touch point | How |
|---|---|
| `User` | Search, create, `resolveCustomer`, `Auth::user()` channel fields |
| Spatie `Role` | Seeded `Sales Specialist`; create-customer `firstOrCreate` `Customer` `web` |
| `User::role` / `hasRole` | Customer search; `assertCustomerRole`; middleware `hasAnyRole` |
| `UserGroup` | `slug` `customer` + `groups()->syncWithoutDetaching` |
| Access levels | **Not** used on this prefix |

## catalog-products

| Touch point | How |
|---|---|
| `Product::forChannel` + `status=active` | `prepareCart` `firstOrFail`; `OrderController::searchProducts` |
| Create-form JS | `GET /api/v1/products/search` and `/api/v1/products-projection` with `X-Channel` / `Accept-Language` (public catalog API, not the specialist search route) |
| `CartService::addItem` | Configurable parent / rental-only / pricing via `effectiveUnitPrice` (reads `catalog_rule_product_prices`) |

## cart-checkout

| Touch point | How |
|---|---|
| `Cart` scratch row | `session_token` `sales-specialist:{authId}` |
| `CartService` | `setCustomer`, `addItem`, `setAddresses`, `recalcTotals`, `shippingQuotes`, `setShippingMethod`, `setPaymentMethod` |
| `OrderService::place` | Only place entry from this desk |
| `CheckoutAddressFields` | Guest flag; FormRequest address rules; create Blade field visibility |
| `PersonName::split` | Prefill billing from customer display name |

## orders

| Touch point | How |
|---|---|
| `Order` | Dashboard/index/show; `ownedBySalesSpecialist`; `STATUS_*`; `sourceLabel()` |
| Columns written at place | `sales_specialist_id`, `source` via `OrderService` opts from this module only |
| Show Blade | Reuses `admin.orders._items_table`, `_totals`, `_customer`, `_status_badge` |
| After place | `OrderObserver` / invoices / assignments — orders module, not called from these controllers |

## channels-locales

| Touch point | How |
|---|---|
| `SetCurrentChannel` | Web group; binds `currentChannel` from `User::currentChannel` |
| `ResolvesSalesChannel` / `ensureChannelBound` | Fallback to first user channel or `Channel::orderBy('id')->first()`; may `syncWithoutDetaching` |
| `set.user.locale` | Specialist route group |
| `Channel` model | Fallback query; create view `name` / JS `code` |
| `forChannel` | Products, shipping methods, payment methods |
| Header switch | Admin-only routes; specialists cannot switch via those links |

## payments

| Touch point | How |
|---|---|
| `PaymentMethod::active()->forChannel` | Fulfillment preview; `StoreOrderRequest` after-validation |
| `PaymentDriverManager` | Preview filter; unrouted `searchPaymentMethods`; unused type-hint on `create()` |
| `RedirectPaymentDriver` | Excluded in preview (`RaiAcceptDriver`, `PcbBankDriver`) |
| `CartService::setPaymentMethod` | Initiate + `CartPayment` before place |

## shipping

| Touch point | How |
|---|---|
| `ShippingMethod::active()->forChannel` | `StoreOrderRequest`; unrouted `searchShippingMethods` |
| `CartService::shippingQuotes` / `setShippingMethod` | Drivers and country exclusion live in shipping/checkout |

## tax (verified, extra to the original sibling list)

| Touch point | How |
|---|---|
| `TaxService` | Injected; `calculate` in `buildPreviewResponse` |
| `tax_config('price_includes_tax', true)` | Preview JSON flag |
| Place-time tax | Recalculated inside `OrderService`, not duplicated here |

## Adjacent HTTP (not PHP constructor deps)

| Call from Blade | Owner |
|---|---|
| `GET /api/v1/countries`, `GET /api/v1/countries/{code}/cities` | `App\Http\Controllers\Api\V1\CountryController` (`Country`, `City`). There is no `docs/modules/` folder for that catalog in the current repository. |
| `GET /api/v1/products/search`, `GET /api/v1/products-projection` | [`../catalog-products/README.md`](../catalog-products/README.md) |

## Not used by this module’s PHP

- Coupon apply/remove
- Rental reservation APIs
- Admin `check.permission`
- Storefront `CheckoutController::placeOrder`

`catalog-rules-pricing` is not imported; unit prices still flow through `CartService::effectiveUnitPrice` when items are added.
