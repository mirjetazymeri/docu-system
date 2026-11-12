# Orders — services

## `App\Services\InvoiceService`

Owned here. Used by admin invoice actions and by `MailService::sendPaymentConfirmation()` ([shared mail](../../shared/README.md#mail) / [payments](../payments/)).

Package: `barryvdh/laravel-dompdf` `^3.1` (`composer.json`). Facade `Barryvdh\DomPDF\Facade\Pdf`.

| Method | Behavior |
|---|---|
| `generate(Order)` | Loads items, addresses, payments, user. Builds `$store` from `setting()` (`store.company_name`, `general.logo`/`app_name`, `store.email`/`phone`/`address`/`city`/`postcode`/`country`/`vat_number`/`registration_number`/`bank_*`, `store.invoice_footer_note`). Invoice number = `setting('store.invoice_prefix', 'INV-')` + `order.number`. Receipt = `PaymentReceipt::fromOrderPayment(latest payment)`. Renders `invoices.invoice`. Paper A4 portrait. Writes `local` disk `invoices/INV-{number}.pdf`. Returns storage path. |
| `exists(Order)` | Same relative path if file exists, else null |
| `path(Order)` | Absolute filesystem path or null |
| `delete(Order)` | Deletes stored file if present. **No controller calls this.** |

Invoice date in the view: `paid_at ?? placed_at ?? created_at`.

`PaymentReceipt` (payments module) maps `order_payments.data.pcbbank` / `raiaccept` into card/approval fields for the PDF and payment-confirmation mail. Offline/COD rows typically yield no receipt.

---

## `App\Services\OrderShipmentAssignmentService`

Constructor: `ShippingDriverManager` ([shipping](../shipping/)).

### `syncToShipment(OrderShipmentAssignment): array`

Loads shipment+order and shipping method.

Throws `ValidationException` if:

- missing shipment/order
- order `isClosed()`
- shipment is `delivered` or `canceled` **and** assignment status differs

Inside a DB transaction:

- Copy assignment `status` onto shipment.
- If assignment has `tracking_number`, copy it; else if status is `shipped` and shipment has no tracking, generate via `OrderShipment::generateTrackingNumber` using assignment method code or shipment carrier or `SHP`.
- Merge `meta.internal_assignment`: assignment id, method id/name, `external_reference`, `synced_at`.
- Set `shipped_at` / `delivered_at` like admin `updateShipment`.
- If shipped and order can → `STATUS_SHIPPED`; if delivered and can → `STATUS_COMPLETED`.

Then `dispatchToProvider()` on a fresh assignment (outside the status transaction’s provider call — provider runs after commit of that transaction).

Returns `['synced' => true, 'provider' => …]`.

### `dispatchToProvider`

- No shipping method → `missing_shipping_method`
- Driver without `submitAssignment` → `driver_does_not_support_submission`
- Else call `submitAssignment($assignment)`. Success: `attempted`, `submitted` from result (default true).
- On `Throwable`: `recordProviderFailure` writes `shipment.meta.provider_submission` (`status=failed`, error, `failed_at`), `report($e)`, return `reason = provider_submit_failed`.

Known implementor in this codebase: `App\Services\Shipping\Drivers\BekiPostApiDriver::submitAssignment` (token, city resolve, AddOrder; may set `external_reference` / tracking and `meta.beki`). Config flag `submit_on_assignment` can skip submit. Details: [shipping](../shipping/).

---

## `App\Services\Checkout\CreditNoteService`

**Order-owned behavior.** Namespace is `Checkout` but the only production caller is `Admin\OrderController::refund`. Checkout place-order does not call it. Documented here; [cart-checkout](../cart-checkout/) should only link.

Depends on `App\Domain\Pricing\DiscountAllocator` and `Money` (shared pricing domain, also used by catalog-rules-pricing / checkout).

### `quote(Order, array $input): array`

Non-persisting preview. Builds lines (or monetary allocation). Optional `include_shipping` adds remaining shipping gross/tax. Returns `lines`, `subtotal`, `discount_total`, `tax_total`, `shipping_total`, `shipping_tax`, `grand_total` as decimal strings.

### `issue(Order, array $input): CreditNote`

`$input`: `reason?`, `restock?`, `include_shipping?`, `amount?`, `items?: list{order_item_id, qty}`.

Guards:

- Canceled orders throw. Non-refundable status throws (`isRefundable()`).
- `refundableAmount() <= 0` → nothing left.
- Empty lines and no shipping → no refundable lines.

Caps grand total to `refundableAmount()`. If `amount` is set:

- empty items + no shipping → `allocateMonetaryAcrossRemaining` then grand = target
- else amount must match computed item credit (±0.009) or throw

Transaction:

1. `CreditNote` `issued`, number `CN-{order.number}-{nn}`.
2. `CreditNoteItem` rows (proportional `proportionLine`: share of `row_total`/`discount`/`tax`; prefers `meta.net_total`).
3. Negative `OrderPayment` `STATUS_REFUNDED`, `data.refunded_by = admin`, `credit_note_id` / `credit_note` number. Method copied from `order.payment` (latest payment).
4. Link `order_payment_id`.
5. If `restock`: increment `ProductInventory` by line `product_id` (creates row via `firstOrNew`). **Does not skip rental lines.** Sets `restocked = true`.
6. Refresh order; if `refundableAmount() <= 0` and `canTransitionTo(refunded)`, `transitionTo(refunded)`. Partial credit notes **leave** order status unchanged.

`remainingQty`: ordered qty minus issued credit-note item qty.

`remainingShipping`: order `shipping_total` minus issued notes’ `shipping_total`; shipping tax ≈ `order.tax_total - sum(item.tax)` minus issued `shipping_tax`.

Admin `mode`:

| `mode` | Controller payload |
|---|---|
| `full` (or empty items without amount — service default) | `include_shipping = true`; service refunds all remaining qty on every line + shipping remainder |
| `items` | filtered positive qty rows; requires ≥1 |
| `amount` (default if no items) | requires `amount`; monetary allocation across remaining lines |

Controller also passes `reason`, `restock`, `include_shipping` (full forces shipping true).
