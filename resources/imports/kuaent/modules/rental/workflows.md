# Rental — workflows

Call chains match the PHP methods. Inclusive vs exclusive end dates: `Product::rentalEndInclusive()` on both billing (`periodsFor`) and overlap (`scopeOverlapping`).

---

## 1. Admin configures rates

`ProductController::store` / `update` → `ProductService::syncRentalPrices`. Configurable parents also `ConfigurableProductService::syncChildRentalFromParent`.

`sale_mode = sale` deletes all `product_rental_prices` for that product (and children, on variant sync). Rentable products require `rental_prices` on the FormRequest. Rates are limited to the admin current channel.

Projection rebuild follows product save. Rental-only SKUs with no buy price use the channel `rate` as projection list price.

---

## 2. Storefront quote (no hold)

`GET /api/v1/products/{id}/rental-availability` → `availableUnits` + `quote`. No `RentalReservation` insert. Own cart holds **do** reduce `available_units` here (no `excludeCartId`).

---

## 3. Hold (add to cart)

```
CartController::addItem
  → CartService::addItem (reject configurable parent)
  → addRentalItem
       isRentable
       RentalAvailabilityService::quote
       availableUnits(excludeCartId = this cart)
       insert CartItem (line_type=rental, quote snapshot)
       insert RentalReservation held, expires_at = now+120m
       recalcTotals (deposit_total)
```

Distinct date ranges are separate lines (add never calls `findMatchingLine`).

Qty update: `updateRentalQty` — dates fixed; re-check availability; scale `row_total` from existing `unit_price`; refresh hold `qty` + `expires_at`. Qty ≤ 0 or remove: `releaseReservationsForItem` (`held` → `cancelled`).

Cart clear: all held rows for `cart_id` → `cancelled`.

Guest merge: replicate rental `CartItem` onto target; retarget **held** reservations to the new `cart_id` / `cart_item_id`.

---

## 4. Checkout confirm

```
OrderService::place
  lock cart
  for rental lines:
    availableUnits(excludeCartId = this cart)
    skip inventory + sale price check
  create Order (deposit_total from tax quote)
  create OrderItem (rental snapshot)
  RentalReservation where cart_item_id AND status=held
    → confirmed, order_id, order_item_id, expires_at=null
  skip deductProductStock
  cart status=ordered
```

If the scheduler already cancelled the hold, the `where status=held` update matches **zero** rows. The order line is still created.

Place does not rebuild projections for rental-only products (`$affectedProductIds` skips them).

---

## 5. Release expired holds

Every 10 minutes: `rentals:release-expired-holds`.

`held` + `expires_at < now()` → `cancelled`. Calendar frees. Cart lines, if still present, are unchanged. Later place re-checks availability; it will not resurrect a cancelled hold.

`--dry-run` counts only.

---

## 6. Admin return

`POST admin.orders.rentals.return`

Only `confirmed` → `returned`. Then `ProductProjectionService::rebuild` after commit. Order status unchanged.

`held` / `returned` / `cancelled` cannot be marked returned (422).

---

## 7. Order cancel / credit note (adjacent)

| Path | Reservations | Inventory |
|---|---|---|
| `OrderController::performCancel` | unchanged | rental lines skipped |
| `CreditNoteService::issue` restock | unchanged | **does** increment `product_inventory.qty` for credited product ids, including rental |

There is no code path that sets a confirmed reservation to `cancelled` on order cancel.

---

## State machine (reservation)

```
          add to cart
              │
              ▼
           held ──scheduler / clear / remove──► cancelled
              │
              │ place (still held)
              ▼
          confirmed ──admin return──► returned
```

No transitions from `returned` or `cancelled` in application code.
