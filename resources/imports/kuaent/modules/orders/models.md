# Orders — models

All under `app/Models/`. Soft deletes: **`Order` only**.

## `Order`

Table `orders`. Fillable includes number, user, specialist, source, email, status, money columns, tax snapshot, coupon, notes, external sync columns, timestamps (`placed_at`, `paid_at`, `shipped_at`, `completed_at`, `canceled_at`).

### Statuses

| Constant | Value |
|---|---|
| `STATUS_PENDING` | `pending` |
| `STATUS_PROCESSING` | `processing` |
| `STATUS_PAID` | `paid` |
| `STATUS_SHIPPED` | `shipped` |
| `STATUS_COMPLETED` | `completed` |
| `STATUS_CANCELED` | `canceled` |
| `STATUS_REFUNDED` | `refunded` |

`STATUSES`, `STATUS_LABELS`, `STATUS_COLORS` (Bootstrap color tokens for badges).

### Transitions (`TRANSITIONS`)

| From | Allowed next |
|---|---|
| `pending` | `processing`, `paid`, `canceled` |
| `processing` | `paid`, `shipped`, `canceled` |
| `paid` | `processing`, `shipped`, `completed`, `refunded`, `canceled` |
| `shipped` | `completed`, `refunded` |
| `completed` | `refunded` |
| `canceled` | _(none)_ |
| `refunded` | _(none)_ |

`canTransitionTo($target)` / `allowedTransitions()` / `transitionTo($target)`:

- Illegal transition throws `RuntimeException`.
- `paid` sets `paid_at` if empty; `shipped` → `shipped_at`; `completed` → `completed_at`; `canceled` → `canceled_at`. Each uses `??=` (does not overwrite an existing timestamp).
- `refunded` does **not** set a dedicated timestamp column.

`isClosed()` = status in `canceled` or `refunded`.

`isCancellable()` = can transition to `canceled`. `isRefundable()` = can transition to `refunded`.

### Sources

| Constant | Value | Assigned in code? |
|---|---|---|
| `SOURCE_STOREFRONT` | `storefront` | No |
| `SOURCE_API` | `api` | No |
| `SOURCE_SALES_SPECIALIST` | `sales_specialist` | Yes — `SalesSpecialistOrderService::placeForCustomer()` |
| `SOURCE_ADMIN` | `admin` | No |

`sourceLabel()`: mapped label, or “Online” when `source` is empty.

`scopeOwnedBySalesSpecialist($query, int $userId)`: `where sales_specialist_id`.

### Money helpers

| Method | Meaning |
|---|---|
| `totalPaid()` | Sum of **positive** payment rows with status `captured`, `authorized`, or `paid` |
| `totalRefunded()` | Absolute sum of **negative** payment amounts |
| `refundableAmount()` | `max(0, totalPaid - totalRefunded)` — **not** `grand_total` |
| `outstandingAmount()` | `grand_total - totalPaid + totalRefunded` |

`qtyAlreadyRestocked(OrderItem)` / `restockableQtyOnCancel(OrderItem)`: issued credit notes with `restocked = true` so cancel does not double-restock.

### Derived UI states

`paymentState()`: `unpaid` | `partial` | `paid` | `refunded` | `overpaid` (0.009 tolerance vs `grand_total`).

`fulfillmentState()`: `canceled` if order canceled; else from shipments (`unfulfilled` / `processing` / `shipped` / `delivered`).

`statusPipeline()`: UI steps `pending → processing → paid → shipped → completed`; terminal flag for canceled/refunded.

`isExternalOrderSynced()`: `external_order_sync_status === 'synced'`.

`hasRentals()`: any item with `line_type === 'rental'` (uses loaded `items` relation).

### Relations

- `user()`, `salesSpecialist()` (both `User`; specialist via `sales_specialist_id`)
- `items()`, `addresses()`, `billingAddress()`, `shippingAddress()`
- `payments()`, `payment()` (`hasOne` latest of many)
- `shipments()`, `shipmentAssignments()`
- `creditNotes()`, `rentalReservations()`

## `OrderItem`

Fillable: `order_id`, `product_id`, `line_type`, sku/name/qty/prices, `meta` (array), rental dates/periods/unit, `deposit_total`.

`isRental()`: `line_type === 'rental'`.

Relations: `order()`, `product()`.

Checkout stores in `meta` (when present): `options`, `customization`, `bundle` (child snapshot), `net_total`, `tax_total`. Credit notes prefer `meta.net_total` for proportional net/tax.

## `OrderAddress`

`type` enum `billing` | `shipping`. Unique `(order_id, type)`. `data` JSON snapshot. Admin edit validates `first_name`, `last_name`, `phone`, `address1`, `address2`, `city`, `postcode`, `country` (ISO-2), `company`, `vat`. Migration comment also mentions `region` / `vat_id`; those keys are not in the admin validator.

## `OrderPayment`

Statuses:

| Constant | Value |
|---|---|
| `STATUS_PENDING` | `pending` |
| `STATUS_AUTH` | `authorized` |
| `STATUS_CAPTURED` | `captured` |
| `STATUS_FAILED` | `failed` |
| `STATUS_REFUNDED` | `refunded` |
| `STATUS_CANCELED` | `canceled` |

Credit notes create a row with `status = refunded` and **negative** `amount`.

`isManuallyManaged()`: `data.marked_by === 'admin'` or `data.refunded_by === 'admin'`.

`isGatewayManaged()`: false if manually managed; else true if `data` has `pcbbank`, `raiaccept`, or non-empty `gateway`; else true if the payment method’s driver config has a `callback_handler` (`PaymentGatewayRegistry`). Admin cannot PATCH those rows.

Relations: `order()`, `paymentMethod()`.

## `OrderShipment`

Statuses: `pending`, `packed`, `shipped`, `delivered`, `canceled`.

Fillable includes `shipping_method_id`, carrier fields, `amount`, tracking, `tracking_url`, `eta_text`, `meta`, `shipped_at`, `delivered_at`.

`generateTrackingNumber($carrierCode, $orderId)`: `TRK-{prefix}-{base36 timestamp}{5 hex}-{orderId}`. Prefix = first 3 chars of carrier code or `SHP`.

Relations: `order()`, `shippingMethod()`, `assignment()` (`hasOne`).

`tracking_number` is **unique** (NULLs allowed).

## `OrderShipmentAssignment`

Uses `HasFactory` but **no** factory class exists under `database/factories/`.

Statuses (same strings as shipment): `pending`, `packed`, `shipped`, `delivered`, `canceled`.

Fillable: `order_id`, `order_shipment_id`, `shipping_method_id`, `status`, `tracking_number`, `external_reference`.

`order_shipment_id` is unique (one assignment per shipment).

Relations: `order()`, `shipment()`, `shippingMethod()`.

## `CreditNote`

Statuses: `STATUS_ISSUED = issued`, `STATUS_VOID = void`.

`STATUS_VOID` is **defined only**; no application code sets or filters void notes. Issue path always creates `issued`.

Number format from `CreditNoteService::nextNumber()`: `CN-{order.number}-{seq}` with seq 2-digit padded per order.

`restocked` boolean: inventory was incremented when the note was issued.

Relations: `order()`, `items()`, `payment()` (`order_payment_id`), `creator()` (`created_by` → `users`).

## `CreditNoteItem`

Line snapshot: sku, name, qty, `unit_gross`, `line_gross`, `line_discount`, `line_net`, `line_tax`, `line_total`, `meta`.

Relations: `creditNote()`, `orderItem()`.

## Related models (not owned)

- `RentalReservation` — `order_id` / `order_item_id`; admin `returnRental` sets `status = returned` then rebuilds product projection.
- `ProductInventory` — incremented on cancel restock and optional credit-note restock.
- `CouponUsage` — `order_id` set at place (`status = consumed`).
- `CartCustomizationUpload` — `order_id` attached at place.
- `Cart` — `order_id` after place.
