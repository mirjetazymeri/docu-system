# Rental — dependencies

Verified against callers and types. Rental is not a Composer package. Relative links are sibling folders under `docs/modules/`.

## [catalog-products](../catalog-products/README.md) — **depends** (and catalog writes rates)

| Use | Where |
|---|---|
| `Product` `sale_mode`, `rental_end_inclusive`, `isRentable()`, `rentalEndInclusive()`, `rentalPriceForChannel()`, inventories | `RentalAvailabilityService`, availability controller, cart/order services |
| `Product::rentalPrices()` / `rentalReservations()` | Models |
| `ProductService::syncRentalPrices` | Admin save |
| `ConfigurableProductService::syncChildRentalFromParent` | Variant copy of rates |
| `ProductPayloadBuilder::buildRental` | Projection `rental` block |
| `ProductProjectionService` | List-price fallback to rental `rate`; `rebuild` after admin return |
| `StoreProductRequest` / product admin form | Rate validation and UI |
| `ProductInventory` qty | `totalUnits()` — warehouse rows owned by warehouses-inventory, written by catalog |

Catalog still **stores** `sale_mode`, `rental_end_inclusive`, and `product_rental_prices`. Calendar/holds/quotes are this module.

## [cart-checkout](../cart-checkout/README.md) — **consumed by** / **writes holds**

| Use | Where |
|---|---|
| `CartService` hold/qty/remove/merge | Inserts and cancels `RentalReservation`; `RENTAL_HOLD_MINUTES = 120` |
| `CartController::addItem` / `clear` | Rental payload; cancel held on abandon |
| `AddCartItemRequest` | `line_type`, `rental_start_date`, `rental_end_date` |
| `Cart` / `CartItem` rental columns | Snapshots + `deposit_total` |
| `OrderService::place` | Confirm held → `confirmed`; copy line snapshot |
| `TaxService` + `OrderTotalsCalculator` | Cart/order `deposit_total` in grand total |

Checkout **creates** orders. This module does not own place-order HTTP.

## [orders](../orders/README.md) — **consumed by**

| Use | Where |
|---|---|
| `Order` `deposit_total`, `rentalReservations()`, `hasRentals()` | Show + totals |
| `OrderItem` rental columns / `isRental()` | Snapshot; cancel skip restock |
| `OrderController::returnRental` | `confirmed` → `returned` |
| `OrderController::performCancel` | Skips rental restock; does not cancel reservations |
| `CreditNoteService` | Restock does not skip rental product ids |
| Admin views `_rentals`, `_items_table`, `_totals` | Return UI |

## Other couplings (verified, not the three named owners)

| Piece | Relation |
|---|---|
| `Channel` / `app('currentChannel')` | Rate row `channel_id`; quote channel; reservation `channel_id`; API 400 if unresolved. Availability **overlap query does not filter channel**. |
| `ProductInventory` | `qty` sum = rentable units. Rental never writes `reserved_qty`. |
| Permissions `product_*`, `order_edit` | Admin rates and return. No `rental_*` permission. |

## Environment

No `RENTAL_*` (or other rental-specific) environment variable names are read. Hold length is `CartService::RENTAL_HOLD_MINUTES`.

## Direction summary

```
catalog-products  ──sale_mode, rates, inventory qty──►  rental (quote/calendar)
rental            ──RentalAvailabilityService────────►  cart-checkout (hold)
cart-checkout     ──confirm at place─────────────────►  rental_reservations + orders
orders            ──returnRental─────────────────────►  reservation returned + projection rebuild
scheduler         ──rentals:release-expired-holds────►  held → cancelled
```
