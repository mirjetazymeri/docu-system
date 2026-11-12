# Rental — controllers

## `App\Http\Controllers\Api\V1\ProductRentalAvailabilityController`

Constructor injects `RentalAvailabilityService`.

### `show(Request $request, int $id)`

`GET /api/v1/products/{id}/rental-availability`

Inline `validate()` (no FormRequest):

| Field | Rules |
|---|---|
| `start` | required, date |
| `end` | required, date, `after_or_equal:start` |
| `qty` | sometimes, integer, min 1, max 9999 |

Loads `Product` with `inventories`, `rentalPrices`. Missing product → 404 (`findOrFail`).

If `! $product->isRentable()` → `ValidationException` on `product`: `This product is not available to rent.`

Channel: `(int) (app('currentChannel')?->id ?? abort(400, 'Channel not resolved'))`.

Then `availableUnits($product, start, end)` (no cart exclusion) and `quote($product, channelId, start, end, qty)` with `qty` default 1. `InvalidArgumentException` from quote/normalize → `ValidationException` on `rental` with the exception message.

JSON body: see [api.md](api.md).

No auth middleware. Group middleware: `channel.api`, `locale.api`.

---

## `App\Http\Controllers\Admin\OrderController` (orders module)

Permission: `order_edit` on `returnRental`. `order_view` on `show` (loads reservations).

### `show(Order $order)`

Eager-loads `rentalReservations.product.translations`. Blade `admin.orders.show` includes `admin.orders._rentals` **only when** `$order->rentalReservations` is not empty. Partial lists product name, date range, qty, status badge, and a return button for `confirmed` when `@can('order_edit')`.

Items table (`_items_table`): rental badge, dates, periods/unit, line deposit. Totals (`_totals`): order `deposit_total` when > 0.

### `returnRental(Request $req, Order $order, RentalReservation $reservation)`

`POST admin/orders/{order}/rentals/{reservation}/return` — named `admin.orders.rentals.return`.

1. `abort_unless((int) $reservation->order_id === (int) $order->id, 404)`.
2. Status must be `STATUS_CONFIRMED`; otherwise `ValidationException` on `rental`: `This rental is "{status}" and cannot be marked as returned.`
3. Transaction: `status = returned`, `save()`.
4. `DB::afterCommit`: `ProductProjectionService::rebuild($reservation->product)`.
5. Redirect back, flash `Rental marked as returned. Availability updated.`

Does **not** change order status, inventory, or payments. Projection `rebuild` uses inventory/price payload, not the booking calendar; calendar occupancy changes because `returned` is not an active status.

### `performCancel` (rental side effect)

For each item, `if ($item->isRental()) continue;` — no inventory restock. **Does not** update `rental_reservations`.

---

## `App\Http\Controllers\Admin\ProductController` (catalog-products)

No rental-specific action. Create/edit form includes `admin.products._rental` (end-date counting + per-channel rates).

- `create` / `edit`: `$channels = collect([auth()->user()->currentChannel])` (edit also loads `rentalPrices`).
- `store` / `update`: `StoreProductRequest` / `UpdateProductRequest` → `ProductService::create` / `update` (syncs `rental_prices`).

Permissions: `product_create` / `product_edit`.

`StoreProductRequest` rental rules (also inherited by `UpdateProductRequest`):

| Field | Rules |
|---|---|
| `sale_mode` | required, `in:sale,rental,both` |
| `rental_end_inclusive` | sometimes, boolean; `prepareForValidation` defaults to `true` if omitted |
| `rental_prices` | `requiredIf` rentable; array. Non-rentable submissions are normalized to `[]` |
| `rental_prices.*.channel_id` | required, exists `channels` |
| `rental_prices.*.period_unit` | required, `in:day,week,month` |
| `rental_prices.*.rate` | required, numeric, min 0 |
| `rental_prices.*.deposit` | nullable, numeric, min 0 |
| `rental_prices.*.min_period` | nullable, integer, min 1 (empty rows skipped; min defaults to 1) |
| `rental_prices.*.max_period` | nullable, integer, min 1, `gte` min_period |
| `rental_prices.*.currency` | required, string, max 3 |

After validation: rental `channel_id`s must be exactly the admin’s `current_channel_id` or the set is empty (`Rental pricing can only be set for the current channel.`).

---

## `App\Http\Controllers\Api\V1\CartController` (cart-checkout)

### `addItem(AddCartItemRequest $req)`

If `line_type === 'rental'`, passes `['start_date' => rental_start_date, 'end_date' => rental_end_date]` into `CartService::addItem`; otherwise `$rental = null`. `RuntimeException` → 422 `{ message }`.

`AddCartItemRequest`: `line_type` sometimes `in:sale,rental`. When rental, `rental_start_date` / `rental_end_date` required dates with end `after_or_equal` start; when not rental those fields are `prohibited`.

### `updateItem` / `removeItem`

`CartService::updateQty` / `removeItem` (hold refresh / cancel).

### `clear`

Transaction: all `RentalReservation` for the cart with `status = held` → `cancelled`; delete items and payment; zero totals including `deposit_total`; `status = abandoned`. Admin cart status patch does **not** do this (cart-checkout).

---

## Views (not controllers)

| Path | Role |
|---|---|
| `resources/views/admin/products/_rental.blade.php` | End inclusive select + rate grid |
| `resources/views/admin/products/_form.blade.php` | `sale_mode` select; rental tab |
| `resources/views/admin/orders/_rentals.blade.php` | Return form |
| `resources/views/admin/orders/_items_table.blade.php` | Line rental badge |
| `resources/views/admin/orders/_totals.blade.php` | Order deposit row |
