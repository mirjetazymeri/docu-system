# Rental — services

## Empty `app/Services/Rental/`

The directory exists and contains **no PHP files**. Nothing in the app namespace `App\Services\Rental`. Treat it as unused. Availability and quoting live in `App\Services\Product\RentalAvailabilityService`.

---

## `App\Services\Product\RentalAvailabilityService`

Docblock: total physical units from inventory; a rental occupies units for its date range; free units = total − reserved (`held` or `confirmed`) overlapping the request.

### `totalUnits(Product $product): int`

Loads `inventories` if missing. Returns `(int) $product->inventories->sum('qty')`. Does **not** subtract `reserved_qty`. Bundles/configurables are not special-cased here (parent inventory is often 0).

### `reservedUnits(Product $product, $start, $end, ?int $excludeReservationId = null, ?int $excludeCartId = null): int`

`RentalReservation` query:

- `product_id` = product ( **not** filtered by `channel_id` )
- `active()` (`held` + `confirmed`)
- `overlapping($start, $end, $product->rentalEndInclusive())`
- optional `id != $excludeReservationId`
- optional: `cart_id` is null **or** `cart_id != $excludeCartId` (this cart’s holds/confirmed rows with that `cart_id` do not count against itself)

`sum('qty')`.

### `availableUnits(...): int`

`max(0, totalUnits - reservedUnits)` with the same exclude arguments.

### `periodsFor(string $unit, $start, $end, bool $inclusive = true): int`

Normalizes range (see below). Day count: inclusive `diffInDays + 1`; exclusive `diffInDays`. Then `max(1, $days)` so same-day exclusive still bills **1**.

| `$unit` | Periods |
|---|---|
| `week` | `max(1, ceil(days / 7))` |
| `month` | `max(1, ceil(days / 30))` |
| anything else (including `day`) | `max(1, days)` |

### `quote(Product $product, int $channelId, $start, $end, int $qty = 1): array`

1. `$price = $product->rentalPriceForChannel($channelId)`. Falsy → `InvalidArgumentException`: `This product is not available to rent on this channel.`
2. Normalize range.
3. `$periods = periodsFor($price->period_unit, ..., $product->rentalEndInclusive())`.
4. If `$periods < (int) $price->min_period` → `Minimum rental period is {n} {unit}(s).`
5. If `max_period !== null` and `$periods > (int) max_period` → `Maximum rental period is {n} {unit}(s).`
6. `qty = max(1, $qty)`.
7. `unit_price = round(rate * periods, 2)`; `line_total = round(unit_price * qty, 2)`; `deposit = round((deposit ?? 0) * qty, 2)`.

Return keys: `period_unit`, `periods`, `rate`, `unit_price`, `line_total`, `deposit`, `currency` (from the price row), `starts_on`, `ends_on` (Y-m-d), `end_date_inclusive`.

### `normalizeRange` (private)

Parse to `CarbonImmutable` `startOfDay`. If end `<` start → `Rental end date must be on or after the start date.`

---

## Catalog writers (catalog-products)

### `ProductService::syncRentalPrices(Product $p, array $rows)` (private)

Called from `create` / `update` after `syncPrices`.

- If `!$p->isRentable()`: delete all `ProductRentalPrice` for the product.
- Else `updateOrCreate` by `channel_id`: `period_unit` default `day`, `rate`, `deposit` (empty string → null), `min_period` at least 1, `max_period` (empty → null), `currency` default `EUR`. Skip rows without `channel_id` or with empty `rate`.
- Delete rows whose `channel_id` is not in the kept list (if kept is empty, the `when` is skipped and **all** remaining rows are deleted — after the loop that kept none).

`onlyProductColumns` persists `sale_mode` (default `sale`) and `rental_end_inclusive` (default `true` if the key is absent).

### `ConfigurableProductService`

On each variant upsert: `syncChildRentalFromParent`.

- `childRentalAttributes`: copy parent `sale_mode` (invalid → `sale`) and `rental_end_inclusive` (payload override or parent, default true).
- If parent mode is not `rental`/`both`: delete child `ProductRentalPrice` rows.
- Else copy parent payload `rental_prices` if present (parent rates may not be saved yet), else loaded parent `rentalPrices`. Same `updateOrCreate` / delete-stale pattern as `ProductService`.

### `ProductPayloadBuilder::buildRental` (private)

Projection payload key `rental`: `null` if not rentable or `rentalPriceForChannel` is empty. Else `period_unit`, `rate`, `deposit` (null if column null), `min_period`, `max_period`, `currency`, `end_date_inclusive`.

### `ProductProjectionService`

If buy `basePrice` and `defaultFinal` are both ≤ 0 and `isRentable()`, uses channel rental `rate` as list price so rental-only SKUs remain projectable. Place-order does **not** add rental product ids to the projection rebuild list (no inventory change). Admin return **does** call `rebuild($reservation->product)`.

---

## Checkout writers (cart-checkout)

### `CartService`

Constant `RENTAL_HOLD_MINUTES = 120`.

**`addItem`:** rejects `Product::isConfigurable()` before the rental branch. If `$rental !== null` → `addRentalItem`. Else requires `isSaleable()` (`Product {sku} can only be rented.`).

**`addRentalItem`:** requires `isRentable()` (`Product {sku} is not available to rent.`). Quote via availability service (`InvalidArgumentException` → `RuntimeException`). `availableUnits(..., excludeCartId: cart.id)`. Insufficient → `Only {n} unit(s) of {sku} are available for the selected dates.` Always **inserts** a new `CartItem` (`line_type = rental`, quote snapshots). Creates `RentalReservation` `held` with `expires_at = now() + 120 minutes`. Recalc totals.

**`updateRentalQty`:** availability with `excludeCartId`. Scales `row_total = unit_price * qty` (does **not** re-quote rate/periods). Deposit = `(rate?->deposit ?? 0) * qty` from `rentalPriceForChannel(cart.channel_id)`. Updates **held** reservation `qty` and refreshes `expires_at`.

**`releaseReservationsForItem`:** held rows for `cart_item_id` → `cancelled`. Used when qty ≤ 0 or remove.

**`mergeInto`:** rental source lines are `replicate`d onto the target (not merged by product). Held reservations for the old `cart_item_id` get `cart_id` / `cart_item_id` of the new line. Source cart is marked `merged`; source items are **not** deleted in this method.

**`recalcTotals`:** `TaxService::calculate` sums `cart.items.deposit_total` into `deposit_total` / `grand_total`.

### `OrderService::place`

For `CartItem::isRental()`:

- Calendar check: `availableUnits(..., excludeCartId: cart.id)`. Fail → `Selected rental dates for {sku} are no longer available.`
- **Skips** sellable stock and `effectiveUnitPrice` equality checks.
- Copies `line_type`, rental dates/periods/unit, `deposit_total` onto `OrderItem`.
- Updates reservations `where cart_item_id` and `status = held` to `confirmed`, sets `order_id`, `order_item_id`, `expires_at = null`.
- **Does not** call `deductProductStock` and **does not** push the product id onto `$affectedProductIds` (no projection rebuild for rental-only place).

Order header `deposit_total` comes from the tax quote (fallback cart `deposit_total`).

---

## Totals / tax (not rental-owned)

`TaxService::calculate` passes `depositTotal: (string) $cart->items->sum('deposit_total')` into `OrderTotalsCalculator`.

Grand total (from `OrderTotalsCalculator` docblock): tax-inclusive `subtotal - discount + shipping + deposit`; tax-exclusive adds tax as well. Deposit is **not** a taxable line.

`CouponService` has no `line_type` / rental branch. Whether a coupon applies is coupon-rule logic, not rental code.

---

## Credit notes (orders)

`CreditNoteService::issue` restock loops credited `product_id`s with **no** `isRental()` skip. Cancel restock **does** skip rental lines. Neither path changes reservation status.
