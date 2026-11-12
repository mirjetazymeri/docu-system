# Rental — tests

There is **no** dedicated rental test class. Searched `tests/` for `RentalReservation`, `ProductRentalPrice`, `RentalAvailabilityService`, `ReleaseExpiredRentalHolds`, `rental-availability`, `returnRental`, `rentals:release-expired-holds`.

Matches are only the configurable catalog tests below.

## What exists

### `tests/Feature/Configurable/ConfigurableProductTest.php`

Catalog-owned. Hits `ProductService` + `ConfigurableProductService` rental **rate copy**, not the calendar.

#### `test_configurable_rental_parent_copies_rates_onto_variants`

Creates a configurable parent with `sale_mode = both`, `rental_end_inclusive = false`, one `rental_prices` row (`day`, rate 25, deposit 150, min 2, max 10, EUR). Asserts each variant child:

- `sale_mode === 'both'`
- `rental_end_inclusive === false`
- one `rentalPrices` row matching those fields

#### `test_updating_parent_rental_rates_refreshes_variant_children`

Parent created `sale_mode = rental`, inclusive true, day rate 10. Update to exclusive false, week rate 90, deposit 200, max 4. Child `fresh(['rentalPrices'])` matches the new parent rates and `sale_mode`.

These tests do **not** call `RentalAvailabilityService`, cart add, place-order, return, or the Artisan command.

## Missing (observed)

No tests for:

- `RentalAvailabilityService` (`totalUnits`, overlap inclusive/exclusive, `periodsFor` day/week/month, min/max period, excludeCartId)
- `GET /api/v1/products/{id}/rental-availability`
- `CartService::addRentalItem` / hold TTL / qty update / clear / merge
- `OrderService::place` confirm vs expired-cancelled hold
- `OrderController::returnRental`
- `rentals:release-expired-holds` (`--dry-run` and update)
- Order cancel leaving reservations `confirmed`
- Credit-note restock of rental product ids

No `ProductRentalPrice` / `RentalReservation` factories.

## How to run the existing tests

```bash
php artisan test --filter=configurable_rental
```
