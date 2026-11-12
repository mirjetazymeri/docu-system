# Orders — requests and validation

There is **one** FormRequest in this module. Admin order and assignment endpoints use `$request->validate()` / nested `validator()`.

`PlaceOrderRequest` and sales-specialist `StoreOrderRequest` belong to [cart-checkout](../cart-checkout/) and [sales-specialist](../sales-specialist/).

## `App\Http\Requests\Api\V1\Order\ListMyOrdersRequest`

`authorize()`: `true` (route already requires Sanctum).

| Field | Rules |
|---|---|
| `per_page` | sometimes, integer, min 1, max 100 |
| `status` | sometimes, string, max 255 (controller splits on comma; **not** `Rule::in` statuses) |
| `number` | sometimes, string, max 64 |
| `date_from` | sometimes, date |
| `date_to` | sometimes, date, `after_or_equal:date_from` |
| `sort` | sometimes, string, `in:placed_at_desc,placed_at_asc,total_desc,total_asc` |

`show` has no FormRequest. `include` is parsed in the controller (allow-list), not validated.

---

## Admin `OrderController` (inline)

### `bulkStatus`

- `order_ids`: required array min 1
- `order_ids.*`: integer, `exists:orders,id`
- `status`: required, `Rule::in(Order::STATUSES)`
- Extra: `status === refunded` → custom error (credit notes only)

### `updateStatus`

- `status`: required, in `STATUSES`
- Extra: not `refunded`; must `canTransitionTo`

### `updateAdminNote`

- `admin_note`: nullable, string, max 5000

### `updateCustomerNote`

- `customer_note`: nullable, string, max 2000

### `updateAddresses`

For each of `billing`, `shipping` (required arrays):

| Key | Rules |
|---|---|
| `first_name`, `last_name` | required, string, max 100 |
| `phone` | required, string, max 50 |
| `address1` | required, string, max 255 |
| `address2` | nullable, string, max 255 |
| `city` | required, string, max 150 |
| `postcode` | nullable, string, max 30 |
| `country` | required, string, **size 2** |
| `company` | nullable, string, max 150 |
| `vat` | nullable, string, max 100 |

### `markPaid`

- `payment_method_id`: required, integer, `exists:payment_methods,id` (then must exist in `forCurrentChannel()`)
- `amount`: nullable, numeric, min 0.01, max outstanding
- Extra: must `canTransitionTo(paid)`; outstanding > 0

### `refund`

- `mode`: nullable, `in:amount,items,full`
- `amount`: nullable, numeric, min 0.01, max `refundableAmount()`
- `reason`: nullable, string, max 500
- `restock`, `include_shipping`: nullable, boolean
- `items`: nullable array
- `items.*.order_item_id`: required_with items, integer
- `items.*.qty`: required_with items, integer, min 0
- Extra: items mode needs at least one qty > 0; amount mode needs `amount`; `RuntimeException` from service remapped to `amount`

### `createShipment`

- `carrier_code`: required, string, max 50
- `carrier_name`: required, string, max 100
- `service`: nullable, string, max 100
- `amount`: nullable, numeric, min 0

### `updateShipment`

- `shipping_method_id`: nullable, integer, `exists:shipping_methods,id`
- `status`: required, in pending/packed/shipped/delivered
- `tracking_number`: nullable, string, max 150, unique `order_shipments` ignoring this id
- `tracking_url`: nullable, string, max 500 (column is string 191)

### `updatePaymentStatus`

- `status`: required, in pending/authorized/captured/failed/canceled
- `reference`: nullable, string, max 255
- Extra: not terminal; not gateway-managed

### `returnRental`

No FormRequest. Extra: reservation `order_id` match; status must be `confirmed`.

### `sendExternal`

No body rules. Extra: settings must be manual.

Closed-order guard: `ValidationException` on `status`.

---

## Admin `OrderShipmentAssignmentController` (inline)

### `store`

- `order_shipment_id`: required, `exists:order_shipments,id`, unique `order_shipment_assignments.order_shipment_id`
- `shipping_method_id`: required, `exists:shipping_methods,id`
- `status`: required, `Rule::in(OrderShipmentAssignment::STATUSES)`
- `tracking_number`, `external_reference`: nullable, string, max 191
- Extra: shipment must have `order`; tracking unique on `order_shipments` ignoring this shipment

### `update`

- `status`: required, in STATUSES
- `tracking_number`, `external_reference`: nullable, string, max 191
- `shipping_method_id`: required, `exists:shipping_methods,id`
- Extra: tracking unique on `order_shipments` ignoring assignment’s shipment id

No FormRequest classes under `app/Http/Requests/Admin/` for orders or assignments.
