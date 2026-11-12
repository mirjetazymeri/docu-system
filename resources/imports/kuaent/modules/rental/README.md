# Rental

Product rental pricing, booking-calendar availability, cart/order rental snapshots, holds, checkout confirm, admin return, and expired-hold release.

This is **not** a Laravel package. Rental types live next to catalog/checkout/order code. There is **no** dedicated `app/Services/Rental/` implementation — that directory exists and is empty (unused). The availability/quote service is `App\Services\Product\RentalAvailabilityService`.

Full `Product`, `Cart`, and `Order` models are documented in sibling modules. This folder covers **rental-specific** columns, models, and flows only.

## What this module does

- Per-channel rental **rates** (`product_rental_prices`: unit, rate, deposit, min/max period, currency).
- Product flags `sale_mode` (`sale` | `rental` | `both`) and `rental_end_inclusive`.
- Storefront **availability + quote** for a date range (`GET /api/v1/products/{id}/rental-availability`).
- Cart **rental lines**: quote, hold a `RentalReservation` (`held`, 120 minutes), deposit on the line and cart.
- Place-order: re-check calendar, copy rental snapshot onto `order_items` / `orders.deposit_total`, **confirm** the hold (no inventory deduct).
- Admin **mark returned** (`confirmed` → `returned`) and rebuild the product projection.
- Artisan **`rentals:release-expired-holds`**, scheduled every 10 minutes in `routes/console.php`.

## What it does not own

| Concern | Owner |
|---|---|
| Product CRUD, projections, configurable variant copy of rates | [catalog-products](../catalog-products/README.md) |
| Cart identity, totals/tax, place-order HTTP | [cart-checkout](../cart-checkout/README.md) |
| Admin order list/status/pay/ship/cancel/credit notes | [orders](../orders/README.md) |
| Warehouse CRUD; `product_inventory.reserved_qty` | [warehouses-inventory](../warehouses-inventory/README.md) (rental occupancy is **not** `reserved_qty`) |
| Tax class admin | [tax](../tax/README.md) (deposit is added to grand total; it is not a tax line) |

## Key classes

| Layer | Class |
|---|---|
| Models | `ProductRentalPrice`, `RentalReservation` |
| Availability / quote | `App\Services\Product\RentalAvailabilityService` |
| API | `App\Http\Controllers\Api\V1\ProductRentalAvailabilityController` |
| Command | `App\Console\Commands\ReleaseExpiredRentalHolds` (`rentals:release-expired-holds`) |
| Cart writes | `CartService::addRentalItem`, `updateRentalQty`, `releaseReservationsForItem`, `mergeInto` |
| Place | `OrderService::place()` (rental branch) |
| Admin return | `OrderController::returnRental` |

## Docs in this folder

| File | Contents |
|---|---|
| [models.md](models.md) | `ProductRentalPrice`, `RentalReservation`, rental fields on Product/Cart/Order |
| [database.md](database.md) | Tables and rental columns on product/cart/order tables |
| [controllers.md](controllers.md) | API availability, admin return, product/cart controllers that touch rental |
| [services.md](services.md) | `RentalAvailabilityService` plus catalog/checkout writers |
| [commands.md](commands.md) | `rentals:release-expired-holds` + schedule |
| [routes.md](routes.md) | API, admin, console |
| [api.md](api.md) | Availability endpoint and cart/order rental JSON |
| [workflows.md](workflows.md) | Hold → checkout confirm → expire / return |
| [dependencies.md](dependencies.md) | catalog-products, cart-checkout, orders (verified) |
| [tests.md](tests.md) | What exists (configurable rate-copy only) |

No module-owned jobs (`app/Jobs` has none named rental). No `jobs.md`.

## Permissions

There is **no** `rental_*` permission in `ModulePermissionSeeder`.

| Permission | Use |
|---|---|
| `product_create` / `product_edit` | Admin product form rental tab (`StoreProductRequest` / `UpdateProductRequest`) |
| `product_view` | Edit form loads `rentalPrices` |
| `order_view` | Order show (rentals tab) |
| `order_edit` | `OrderController::returnRental` |

Storefront availability is public (channel + locale middleware only).

## Hard rules from code (do not invent)

- **Hold TTL is 120 minutes**, `CartService::RENTAL_HOLD_MINUTES`. Not an env var. Refreshed on rental qty update. Cleared (`expires_at = null`) when the hold is confirmed at place.
- **Calendar occupancy** = `held` + `confirmed` (`RentalReservation::ACTIVE_STATUSES`). `returned` and `cancelled` do not occupy.
- **Total rentable units** = sum of `product_inventory.qty` across warehouses (`RentalAvailabilityService::totalUnits`). Ignores `reserved_qty`.
- **Place does not deduct inventory** for rental lines. Cancel restock **skips** rental lines. Credit-note restock **does not** skip rental product ids.
- **Order cancel does not change reservation status.** Confirmed rows stay `confirmed` until admin return or a product/channel cascade delete.
- **Place confirms only rows still `held` for that `cart_item_id`.** If the scheduler already set `cancelled`, the order line is still created and **no** reservation is flipped to `confirmed`.
- **Availability queries do not filter `channel_id`.** Overlap is per `product_id` + active statuses + dates.
- **Rental lines are never merged** on add (each add inserts a new `CartItem`). Guest merge copies rental lines as standalone rows and retargets held reservations.
- Never document secrets. This module reads no `RENTAL_*` environment variables.
