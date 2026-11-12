# Orders — routes

## Admin (web)

Group in `routes/web.php`:

```
middleware: auth, verified.if-required, set.user.locale, role:Admin
prefix: admin
name: admin.
```

Plus per-action `check.permission` on the controllers.

### `admin.orders.*` — prefix `admin/orders`

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `/admin/orders` | `admin.orders.index` | `index` |
| GET | `/admin/orders/export/csv` | `admin.orders.export.csv` | `exportCsv` |
| POST | `/admin/orders/bulk-status` | `admin.orders.bulkStatus` | `bulkStatus` |
| GET | `/admin/orders/{order}` | `admin.orders.show` | `show` |
| PATCH | `/admin/orders/{order}/addresses` | `admin.orders.addresses.update` | `updateAddresses` |
| PATCH | `/admin/orders/{order}/status` | `admin.orders.status.update` | `updateStatus` |
| PATCH | `/admin/orders/{order}/note` | `admin.orders.note.update` | `updateCustomerNote` |
| PATCH | `/admin/orders/{order}/admin-note` | `admin.orders.adminNote.update` | `updateAdminNote` |
| POST | `/admin/orders/{order}/mark-paid` | `admin.orders.markPaid` | `markPaid` |
| POST | `/admin/orders/{order}/refund` | `admin.orders.refund` | `refund` |
| POST | `/admin/orders/{order}/cancel` | `admin.orders.cancel` | `cancel` |
| POST | `/admin/orders/{order}/ship` | `admin.orders.ship` | `createShipment` |
| PATCH | `/admin/orders/{order}/shipments/{shipment}` | `admin.orders.shipment.update` | `updateShipment` |
| POST | `/admin/orders/{order}/deliver` | `admin.orders.deliver` | `markDelivered` |
| PATCH | `/admin/orders/{order}/payments/{payment}` | `admin.orders.payment.update` | `updatePaymentStatus` |
| POST | `/admin/orders/{order}/rentals/{reservation}/return` | `admin.orders.rentals.return` | `returnRental` |
| POST | `/admin/orders/{order}/resend-confirmation` | `admin.orders.resend.confirmation` | `resendConfirmation` |
| POST | `/admin/orders/{order}/send-external` | `admin.orders.send.external` | `sendExternal` |
| POST | `/admin/orders/{order}/invoice/generate` | `admin.orders.invoice.generate` | `generateInvoice` |
| GET | `/admin/orders/{order}/invoice/download` | `admin.orders.invoice.download` | `downloadInvoice` |
| GET | `/admin/orders/{order}/invoice/preview` | `admin.orders.invoice.preview` | `previewInvoice` |

Route-model binding: `{order}` → `App\Models\Order` (includes soft-deleted? default binding **excludes** trashed unless `withTrashed`). `{shipment}` → `OrderShipment`; `{payment}` → `OrderPayment`; `{reservation}` → `RentalReservation`. Controller still checks shipment/payment/reservation `order_id`.

Sidebar: Sales → Orders (`admin.orders.index`) if `order_view`.

### `admin.assignments.*`

Same admin group, **not** under the `orders` prefix:

| Method | URI | Name |
|---|---|---|
| GET | `/admin/assignments` | `admin.assignments.index` |
| GET | `/admin/assignments/create` | `admin.assignments.create` |
| POST | `/admin/assignments` | `admin.assignments.store` |
| GET | `/admin/assignments/{assignment}/edit` | `admin.assignments.edit` |
| PUT | `/admin/assignments/{assignment}` | `admin.assignments.update` |

`{assignment}` → `OrderShipmentAssignment`. No DELETE route.

Sidebar: Sales → Shipment assignments if `assignment_view`.

### Lookups used by assignment UI (not named `orders.*`)

| Method | URI | Name | Permission |
|---|---|---|---|
| GET | `/admin/lookups/unassigned-shipments` | `admin.lookups.unassigned-shipments` | `lookup_view` |
| GET | `/admin/lookups/shipping-methods` | `admin.lookups.shipping-methods` | `lookup_view` |

---

## API

`bootstrap/app.php` registers `routes/api.php` with Laravel’s default `/api` prefix.

Group: `prefix v1` + `channel.api` + `locale.api`.

Then `auth:sanctum` → `verified.if-required`:

| Method | URI | Controller |
|---|---|---|
| GET | `/api/v1/me/orders` | `MyOrderController@index` |
| GET | `/api/v1/me/orders/{id}` | `MyOrderController@show` |

`{id}` is an integer **order id**, not `number`. No named routes.

Place-order (`POST /api/v1/checkout/...`) is not in this module.

---

## Sales specialist (dep)

Same `web.php`, `role:Sales Specialist`, prefix `sales-specialist`:

`sales-specialist.orders` resource `index|create|store|show` plus cart-preview / fulfillment-options. Owned by [sales-specialist](../sales-specialist/).
