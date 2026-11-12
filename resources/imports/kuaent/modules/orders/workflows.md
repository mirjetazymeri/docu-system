# Orders — workflows

## 1. Status change

**Entry points:** `PATCH admin.orders.status.update`, `POST admin.orders.bulkStatus`, plus implied transitions from pay/ship/deliver/refund/cancel/assignment sync.

Legal edges: [models.md](models.md) `TRANSITIONS`.

Admin status form **omits** `canceled` and `refunded` from the dropdown (`_actions.blade.php`); cancel and credit-note have their own actions. API of `updateStatus` still rejects setting `refunded` even if posted.

`transitionTo()` persists status + timestamp and fires `OrderObserver::updated` → status mail.

**Not** using the machine:

- Payments finalize jobs: `pending` → `processing` via raw `save()`.
- Those jobs set `paid_at` without moving to `paid`.

Bulk: invalid transitions are skipped (counted), not thrown. `refunded` is rejected for the whole request.

---

## 2. Mark paid (admin)

`POST admin.orders.markPaid`

1. Order must allow transition to `paid`.
2. Outstanding must be > 0 (`grand_total - paid + refunded`).
3. Payment method must exist on **current admin channel**.
4. Insert captured `OrderPayment` with `data.marked_by = admin`.
5. `transitionTo(paid)` even if `amount` is a partial capture.

This row is **not** gateway-managed (`isManuallyManaged()`), so staff can later PATCH its status (except after refunded/canceled).

---

## 3. Payment row update

`PATCH admin.orders.payment.update`

Blocked when closed, terminal payment status, or `isGatewayManaged()` (PCB/RaiAccept data keys, `data.gateway`, or driver `callback_handler`).

Captured + can transition to paid → order becomes `paid`.

Refunds of gateway rows are **not** done here; use credit notes (negative payment row, `refunded_by=admin`).

---

## 4. Ship / deliver

### Checkout-created shipment

`OrderService::place()` inserts one `order_shipments` row: `pending`, carrier snapshot from cart, **no** `shipping_method_id`.

### Admin create extra shipment

`POST admin.orders.ship` → pending row. If order is `pending` or `paid` and can go to `processing` → `processing`.

### Update shipment

`PATCH admin.orders.shipment.update`

- Terminal delivered/canceled shipments cannot change.
- Optional channel-scoped shipping method overwrites `shipping_method_id` + carrier code/name.
- `shipped` without tracking → generated `TRK-…`.
- `shipped` → order `shipped` if allowed (`processing` or `paid` can; `pending` cannot).
- `delivered` → order `completed` if allowed (`paid` can; `shipped` can; `processing` cannot go directly to `completed`).

### Mark delivered (order-level)

`POST admin.orders.deliver`: latest shipment → delivered; order → `completed` if allowed. If there is no shipment, only the order status changes.

### Assignment path

`OrderShipmentAssignmentService::syncToShipment` copies assignment status/tracking onto the shipment and applies the same order transitions, then may submit to the shipping driver. Closed orders cannot be changed via assignment.

---

## 5. Invoice

`POST admin.orders.invoice.generate` / GET download / preview.

No status prerequisite in the controller. `InvoiceService::generate()` overwrites the same path each time (`invoices/INV-{number}.pdf`).

Download/preview generate if the file is missing.

Payment confirmation mail (finalize jobs) also calls `generate()` then attaches the PDF.

Store branding comes from settings keys listed in [services.md](services.md). Do not treat IBAN/bank fields as documentation secrets to copy; they are per-install settings.

---

## 6. Credit note / refund

`POST admin.orders.refund` → `CreditNoteService::issue()`.

Preconditions: `isRefundable()` (`paid`, `shipped`, or `completed` per `TRANSITIONS`). Unpaid orders have `refundableAmount() = 0` because `totalPaid()` ignores pending rows.

Modes (admin form):

- **amount** — allocate target gross across remaining line balances (`DiscountAllocator`).
- **items** — proportional share of selected qty; qty cannot exceed remaining (ordered minus issued CN qty).
- **full** — remaining qty on all lines + remaining shipping (`include_shipping` forced true).

Optional `restock` increments `product_inventories` for credited `product_id`s (including rental product ids; cancel restock **skips** rentals).

Creates issued `credit_notes` + items + negative `order_payments`. When remaining refundable paid amount is 0, order → `refunded`.

Admin cannot bulk-set or PATCH status to `refunded`.

`CreditNote::STATUS_VOID` is unused; there is no void endpoint.

---

## 7. Cancel

`POST admin.orders.cancel` or bulk/status → cancel.

Allowed from `pending`, `processing`, `paid` only.

Restock: sellable lines only, qty minus already restocked credit-note qty, onto the **line product_id** (bundle parents, not children). Pending payments and pending/packed shipments canceled. Order → `canceled`. Rental reservations are not cancelled here.

---

## 8. External catalog send

**Auto:** on order create, `SubmitExternalOrderJob` ([jobs.md](jobs.md)). Admin button disabled (`sendExternal` errors if not manual).

**Manual:** `POST admin.orders.send.external` → `OrderExportService::send()`:

1. Sets `external_order_sync_status = pending`, clears error.
2. POST Elkos payload (exporter-owned).
3. Success: `synced`, `synced_at`, `sync_response`.
4. Failure: `failed`, `sync_error` (message only in DB/flash — do not copy API keys).

UI copy refers to CFMA/Elkos; setting is `product.external_order.mode`.

---

## 9. Rental return

`POST admin.orders.rentals.return`

Only `RentalReservation::STATUS_CONFIRMED` → `returned`. Then `ProductProjectionService::rebuild` after commit so availability updates. Does not change order status.

---

## 10. Customer history

Authenticated user lists/filters own orders ([api.md](api.md)). Read-only. No cancel/pay from this API.
