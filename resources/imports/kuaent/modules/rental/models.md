# Rental — models

All in `app/Models`. Fillable/casts are from the class files.

## `ProductRentalPrice`

One rate row per product × channel. Table `product_rental_prices`.

**Constant:** `UNITS = ['day', 'week', 'month']` (admin FormRequest uses the same set).

**Fillable:** `product_id`, `channel_id`, `period_unit`, `rate`, `deposit`, `min_period`, `max_period`, `currency`.

**Casts:** `rate` / `deposit` `decimal:2`; `min_period` / `max_period` integer.

**Relations:** `product()` → `Product`; `channel()` → `Channel`.

Unique `(product_id, channel_id)` in the migration — a product has at most one period unit and rate per channel.

No `HasFactory`, no observer.

## `RentalReservation`

Booking-calendar row. Table `rental_reservations`.

### Status constants

| Constant | Value | Occupies calendar? |
|---|---|---|
| `STATUS_HELD` | `held` | Yes |
| `STATUS_CONFIRMED` | `confirmed` | Yes |
| `STATUS_RETURNED` | `returned` | No |
| `STATUS_CANCELLED` | `cancelled` | No |

`ACTIVE_STATUSES = [held, confirmed]`. Scope `scopeActive` is `whereIn('status', ACTIVE_STATUSES)`.

**Fillable:** `product_id`, `channel_id`, `cart_id`, `order_id`, `cart_item_id`, `order_item_id`, `qty`, `starts_on`, `ends_on`, `status`, `expires_at`.

**Casts:** `qty` integer; `starts_on` / `ends_on` date; `expires_at` datetime.

**Relations defined on the model:** `product()` → `Product` only. There are **no** `cart()`, `order()`, `channel()`, `cartItem()`, or `orderItem()` methods. Callers query by those FKs / unsigned ids.

No `HasFactory`, no observer.

### `scopeOverlapping($query, $start, $end, bool $inclusive = true)`

Used by `RentalAvailabilityService::reservedUnits` with `$inclusive = $product->rentalEndInclusive()`.

| `$inclusive` | Interval | SQL |
|---|---|---|
| `true` | closed `[start, end]` | `starts_on <= $end` AND `ends_on >= $start` |
| `false` | half-open `[start, end)` | `starts_on < $end` AND `ends_on > $start` |

When exclusive, a booking that **ends** on the day another **begins** does not overlap.

---

## Rental fields on catalog / cart / order models

Owned by other modules; rental-specific surface only. Full models: [catalog-products](../catalog-products/models.md), [cart-checkout](../cart-checkout/models.md), [orders](../orders/models.md).

### `Product`

Constants: `SALE_MODES = ['sale', 'rental', 'both']`.

Rental fillable: `sale_mode`, `rental_end_inclusive` (boolean cast).

| Method | Behaviour |
|---|---|
| `isRentable()` | `sale_mode` in `rental`, `both` |
| `isSaleable()` | `sale_mode` (default `sale`) in `sale`, `both` |
| `rentalEndInclusive()` | `(bool) ($this->rental_end_inclusive ?? true)` |
| `rentalPrices()` | `hasMany(ProductRentalPrice)` |
| `rentalReservations()` | `hasMany(RentalReservation)` |
| `rentalPriceForChannel(?int $channelId = null)` | Channel from argument or `app('currentChannel')->id`. Missing channel → `null`. Prefer own row with `rate > 0`; else configurable parent’s row for that channel; else own row even if rate is 0 |

`rentalPriceForChannel` does **not** check `isRentable()`. Quote/API callers check rentable separately.

### `Cart`

Fillable/cast: `deposit_total` `decimal:2`. Header total is the sum of line deposits via `TaxService` → `OrderTotalsCalculator` (see [services.md](services.md)).

No `rentalReservations()` relation on `Cart`.

### `CartItem`

Fillable: `line_type`, `rental_starts_on`, `rental_ends_on`, `rental_periods`, `rental_period_unit`, `deposit_total`.

Casts: rental dates `date`; `rental_periods` integer; `deposit_total` `decimal:2`.

`isRental()`: `line_type === 'rental'`.

### `Order`

Fillable/cast: `deposit_total` `decimal:2`.

`rentalReservations()`: `hasMany(RentalReservation)`.

`hasRentals()`: loaded `items` contains any `line_type === 'rental'` (defaults missing `line_type` to `sale`).

### `OrderItem`

Same rental fillable/casts as `CartItem` (plus sale money fields). `isRental()`: `line_type === 'rental'`.
