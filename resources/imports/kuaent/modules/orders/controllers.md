# Orders — controllers

## `App\Http\Controllers\Admin\OrderController`

Middleware:

- `order_view`: `index`, `show`, `exportCsv`, `downloadInvoice`, `previewInvoice`
- `order_edit`: `bulkStatus`, `updateAddresses`, `updateStatus`, `updateCustomerNote`, `updateAdminNote`, `markPaid`, `refund`, `cancel`, `createShipment`, `updateShipment`, `markDelivered`, `updatePaymentStatus`, `returnRental`, `resendConfirmation`, `sendExternal`, `generateInvoice`

`guardClosed($order)`: if `isClosed()`, throws `ValidationException` on `status`. Used by address/note/shipment/payment-status/create-shipment.

### `index` — GET `admin/orders`

Non-AJAX: Blade `admin.orders.index` with query echo (`q`, `status`, `source`, `payment`, `fulfillment`, `date_from`, `date_to`, `min_total`, `max_total`), `statusCounts`, and `stats`:

| Stat | Query |
|---|---|
| `all` | `Order::count()` |
| `today` | `placed_at` today |
| `today_revenue` | sum `grand_total` today excluding `canceled` |
| `awaiting_payment` | status in `pending`, `processing` |
| `to_ship` | status in `paid`, `processing` |
| `open` | status not in `canceled`, `refunded`, `completed` |

AJAX: Yajra DataTables on `Order` with `salesSpecialist`, `user`, `payments`, `shipments`, `withCount('items')`. Columns: checkbox, order (number + source badge + specialist), date, customer, payment/fulfillment badges, status, total, actions (`admin.orders._row_actions`).

`applyOrderListFilters()`:

- `q`: `number`, `email`, `coupon_code`, or user name
- `status`, `source`, `date_from`/`date_to` on `placed_at`, `min_total`/`max_total` on `grand_total`
- `payment=unpaid|refunded|paid|partial` (see source; `paid` includes statuses paid/shipped/completed **or** captured/paid payments, excluding refunded)
- `fulfillment=unfulfilled|shipped|delivered`

### `show` — GET `admin/orders/{order}`

Eager-loads listed in [architecture.md](architecture.md). Billing/shipping arrays from address `data`. Prev/next order ids by `id`. Payment methods: `PaymentMethod::forCurrentChannel()`. Shipping methods: `ShippingMethod::active()->forCurrentChannel()`. `externalOrderManual` from settings.

Views: `admin.orders.show` plus partials `_actions`, `_addresses`, `_payments`, `_shipments`, `_credit_notes`, `_rentals`, `_items_table`, `_totals`, `_pipeline`, `_customer`.

### `bulkStatus` — POST `admin/orders/bulk-status`

Validates `order_ids[]` exist, `status` in `Order::STATUSES`. **Rejects `refunded`.** In a transaction: `canceled` uses `performCancel` if cancellable; other targets use `canTransitionTo` + `transitionTo`. Returns counts updated/skipped.

### `updateStatus` — PATCH `admin/orders/{order}/status`

Rejects `refunded`. Invalid transition → validation error. `canceled` delegates to `cancelOrder`. Else `transitionTo`.

### `updateAddresses` / `updateCustomerNote` / `updateAdminNote`

Closed-guarded. Address rules in [requests-validation.md](requests-validation.md). `updateOrCreate` on type billing/shipping. Notes: customer `max:2000`, admin `max:5000`.

### `markPaid`

Requires `canTransitionTo(paid)` and `outstandingAmount() > 0`. Validates `payment_method_id` exists and is `forCurrentChannel()`. Optional `amount` 0.01..outstanding; default = outstanding.

Creates `OrderPayment` with `STATUS_CAPTURED`, `data.marked_by = admin`, then **`transitionTo(paid)` even if the captured amount is less than outstanding**.

### `cancel` / `performCancel`

Requires `isCancellable()`. Transaction:

1. Load items + credit note items.
2. For each **non-rental** item: restock `restockableQtyOnCancel` onto `ProductInventory` for `product_id` (`lockForUpdate`; creates a row at qty 0 if missing). **Does not** reverse bundle child deductions (restocks the line’s `product_id` only).
3. Pending payments → `canceled`.
4. Shipments in `pending`/`packed` → `canceled`.
5. `transitionTo(canceled)`.

Does **not** update `RentalReservation` rows (they stay `confirmed` unless staff uses `returnRental`).

### `refund`

Requires `isRefundable()`. Delegates to `CreditNoteService::issue()` — see [workflows.md](workflows.md) and [services.md](services.md).

### Shipments

`createShipment`: closed-guarded. Required `carrier_code`, `carrier_name`; optional `service`, `amount`. Creates shipment `pending`, `meta = []`. If order is `pending` or `paid` and can go to `processing`, transitions to `processing`.

`updateShipment`: shipment must belong to order (else 404). Cannot modify `delivered` or `canceled` shipments. Optional `shipping_method_id` (must exist **and** `forCurrentChannel()` to apply). Status in pending/packed/shipped/delivered (not `canceled` via this form). Unique `tracking_number` ignoring current row. Empty tracking on `shipped` → `OrderShipment::generateTrackingNumber`. Sets `shipped_at` / `delivered_at`. Order: shipped shipment → `STATUS_SHIPPED` if allowed; delivered → `STATUS_COMPLETED` if allowed.

`markDelivered`: requires `canTransitionTo(completed)`. Updates **latest** shipment (by id) to delivered + `delivered_at`, then `transitionTo(completed)`. Does not iterate all shipments.

### `updatePaymentStatus`

Closed-guarded; payment must belong to order. Terminal `refunded`/`canceled` rows rejected. Gateway-managed rejected. Allowed statuses: pending, authorized, captured, failed, canceled (not `refunded` — use credit notes). Optional `reference`. Sets `data.status_updated_by = admin`. If new status is `captured` and order can go to `paid`, `transitionTo(paid)`.

### `returnRental`

Reservation must belong to order and be `confirmed`. Sets `returned`. After commit: `ProductProjectionService::rebuild($reservation->product)`.

### `resendConfirmation`

`MailService::resendOrderConfirmation()` — bypasses notification toggles. Missing email or send failure → `ValidationException`.

### `sendExternal`

Requires `ExternalCatalogOrderSettings::isManual()`. Else validation error. `OrderExportService::send($order)`; flash error/success from `success` / `error`.

### `exportCsv`

Query filters: `q` (number/email), `status`, `source`, `date_from`/`date_to`. Chunks 500. Headers: Number, Email, Status, Source, Subtotal, Discount, Shipping, Tax, Grand Total, Currency, Placed At, Paid At. Filename `orders-{Ymd_His}.csv`.

Does **not** apply payment/fulfillment/min-max filters used on the DataTable.

### Invoices

`generateInvoice`: `InvoiceService::generate()`.

`downloadInvoice` / `previewInvoice` (`order_view`): if file missing, generate then download (`INV-{number}.pdf`) or `response()->file` inline PDF.

---

## `App\Http\Controllers\Admin\OrderShipmentAssignmentController`

Permissions: `assignment_view` (index), `assignment_create` (create/store), `assignment_edit` (edit/update). **No destroy.**

### `index`

AJAX DataTables: assignment with `order`, `shipment.shippingMethod`, `shippingMethod`. Blade `admin.assignments.index`.

### `store`

Validates unique `order_shipment_id`, required `shipping_method_id`, status in `OrderShipmentAssignment::STATUSES`, optional tracking/external_reference. Shipment must have an order (422). Tracking uniqueness checked against `order_shipments.tracking_number` ignoring this shipment.

Creates assignment with `order_id` from shipment. `OrderShipmentAssignmentService::syncToShipment()`. If provider reason `provider_submit_failed`, flash warning (assignment still saved).

### `update`

Cannot change `order_shipment_id`. Same status/method/tracking/external_reference rules. Sync again.

Create/edit views: `admin.assignments.create` / `edit`; form uses lookup `admin.lookups.unassigned-shipments` (`lookup_view`) and `admin.lookups.shipping-methods`.

---

## `App\Http\Controllers\Api\V1\MyOrderController`

Routes: `auth:sanctum` + `verified.if-required`. Channel/locale middleware still run (`channel.api`, `locale.api`) but orders are **not** filtered by channel.

### `index`

`ListMyOrdersRequest`. `where('user_id', $request->user('sanctum')->id)`. `withCount('items')`. Filters: `number` LIKE, `status` comma-separated `whereIn`, `date_from`/`date_to` on `placed_at`. Sort: `placed_at_desc` (default), `placed_at_asc`, `total_desc`, `total_asc` (`grand_total`). Paginate `per_page` default 20. `OrderSummaryResource` collection.

### `show`

`Request` (no FormRequest). Same user scope. Optional `include` query: only `items`, `addresses`, `payments`, `shipments` (others ignored). **`creditNotes` is not an allowed include** even though `OrderResource` can serialize `credit_notes` when loaded. `firstOrFail` → 404. `OrderResource`.

---

## Not this module

- `App\Http\Controllers\SalesSpecialist\OrderController` — [sales-specialist](../sales-specialist/).
- `App\Http\Controllers\Api\V1\CheckoutController::place` — creates orders; [cart-checkout](../cart-checkout/).
- `App\Http\Controllers\Admin\LookupController::unassignedShipments` — assignment picker; permission `lookup_view`.
- `App\Http\Controllers\Admin\DashboardController` — `SalesStatisticsService` reads `Order` for dashboard charts; `order_view` only gates the “view all orders” button.
