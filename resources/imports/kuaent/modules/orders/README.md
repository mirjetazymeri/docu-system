# Orders

Admin order operations, customer “my orders” API, and shipment assignments.

This module does **not** create storefront orders. Placement lives in [cart-checkout](../cart-checkout/). This module owns the `Order` aggregate after it exists: status, payments ledger edits, shipments, invoices, credit notes, external send, and customer order history.

## Purpose

- Admin list/show for all orders (DataTables, CSV export, filters).
- Status machine (`pending` → … → `completed` / `canceled` / `refunded`).
- Mark paid, update payment rows, issue credit notes (refunds).
- Create/update shipments, mark delivered, shipment assignments (internal carrier + optional provider submit).
- Invoice PDF generate / preview / download (barryvdh/laravel-dompdf).
- Manual send to the external catalog (Elkos) when export mode is manual.
- Resend customer confirmation mail.
- Mark rental reservations returned (calendar + product projection).
- Customer API: `GET /api/v1/me/orders` and `GET /api/v1/me/orders/{id}`.

## Owned vs not owned

| Owned here | Owned elsewhere |
|---|---|
| `app/Http/Controllers/Admin/OrderController.php` | Order **creation**: `app/Services/Checkout/OrderService.php` → [cart-checkout](../cart-checkout/) |
| `app/Http/Controllers/Admin/OrderShipmentAssignmentController.php` | Gateway finalize: `FinalizePcbBankOrderJob`, `FinalizeRaiAcceptOrderJob` → [payments](../payments/) |
| `app/Http/Controllers/Api/V1/MyOrderController.php` | `SubmitExternalOrderJob` class + `OrderExportService` → [external-catalog](../external-catalog/) |
| `app/Services/InvoiceService.php` | Mail send plumbing: `MailService` → [shared mail](../../shared/README.md#mail) |
| `app/Services/OrderShipmentAssignmentService.php` | Sales-specialist place/list/show → [sales-specialist](../sales-specialist/) |
| `app/Services/Checkout/CreditNoteService.php` (order ops; namespace is Checkout) | Tax quote at place-time → [tax](../tax/) |
| `app/Observers/OrderObserver.php` | Shipping drivers (`submitAssignment`) → [shipping](../shipping/) |
| Models: `Order`, `OrderAddress`, `OrderItem`, `OrderPayment`, `OrderShipment`, `OrderShipmentAssignment`, `CreditNote`, `CreditNoteItem` | `RentalReservation` model → [rental](../rental/) |
| Mailables: `app/Mail/Order/*`, `app/Mail/Admin/NewOrderMail.php` | Inventory rows mutated on cancel/refund restock → [warehouses-inventory](../warehouses-inventory/) |
| Resources: `OrderResource`, `OrderSummaryResource` | `FinancialTotalsResource` (shared API envelope) |
| FormRequest: `ListMyOrdersRequest` | Admin `LookupController::unassignedShipments` (lookup permission) |

## Permissions

Seeded in `database/seeders/ModulePermissionSeeder.php`:

| Module | Permissions |
|---|---|
| Orders | `order_view`, `order_edit` |
| Assignments | `assignment_view`, `assignment_create`, `assignment_edit` |

Admin HTTP uses `check.permission:{name}` (`App\Http\Middleware\AutoAuthorizePermission`). Super-admin bypasses. Blade uses `@can('order_view')` etc.

There is **no** `order_create` / `order_delete` permission. Admin does not create or hard-delete orders.

## Docs in this folder

| File | Contents |
|---|---|
| [architecture.md](architecture.md) | Runtime shape, who mutates an order |
| [models.md](models.md) | Eloquent models, status machine, money helpers |
| [database.md](database.md) | Tables and migrations |
| [controllers.md](controllers.md) | Admin + API controllers |
| [services.md](services.md) | Invoice, assignment sync, credit notes |
| [requests-validation.md](requests-validation.md) | FormRequest + inline `validate()` |
| [routes.md](routes.md) | `admin.orders.*`, `admin.assignments.*` |
| [api.md](api.md) | `GET /api/v1/me/orders` |
| [events.md](events.md) | `OrderObserver` |
| [jobs.md](jobs.md) | Job this module **dispatches**; finalize jobs as inbound deps |
| [notifications.md](notifications.md) | Order mailables (not Laravel Notifications) |
| [workflows.md](workflows.md) | Status, ship, pay, invoice, credit note, cancel, external send |
| [dependencies.md](dependencies.md) | Verified module deps |
| [tests.md](tests.md) | What exists (sparse) |

## Hard rules from code (do not invent)

- **Refunded status is never set by bulk/status forms.** `updateStatus` / `bulkStatus` reject `refunded`. Refunds go through credit notes.
- **Closed orders** (`canceled`, `refunded`) cannot be edited (addresses, notes, shipments, payment status). `guardClosed()`.
- **Gateway-managed payment rows** cannot be edited in admin (`OrderPayment::isGatewayManaged()`).
- **Checkout API does not set `orders.source`.** Only sales-specialist placement sets `source = sales_specialist`. `SOURCE_STOREFRONT`, `SOURCE_API`, and `SOURCE_ADMIN` exist as constants but are not assigned in application code. Null source labels as “Online”.
- **`orders` has no `channel_id`.** Channel filtering on admin payment/shipping method pickers uses the admin’s current channel, not a column on the order.
- Never document env secrets (`EXTERNAL_CATALOG_API_KEY`, mail credentials, bank IBAN values). Settings keys are listed; values are not.
