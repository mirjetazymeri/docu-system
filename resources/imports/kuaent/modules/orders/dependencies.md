# Orders — dependencies

Verified from production callers and types. Relative links assume sibling folders under `docs/modules/`.

## This module depends on

### [cart-checkout](../cart-checkout/)

- `App\Services\Checkout\OrderService::place()` creates the aggregate (items, addresses, pending payment, pending shipment, stock deduct, coupon consume, rental confirm, cart `order_id`).
- Checkout API `POST` place-order is the storefront create path (`source` left **null**).
- `CreditNoteService` lives in `app/Services/Checkout/` but is **called only** from admin refund (order-owned).

### [payments](../payments/)

- `PaymentMethod` (admin mark-paid / show picker; `forCurrentChannel()`, `send_invoice`).
- `OrderPayment` gateway `data` (`pcbbank`, `raiaccept`) and `PaymentGatewayRegistry` for `isGatewayManaged()`.
- `PaymentReceipt` on invoices and payment-confirmation mail.
- `FinalizePcbBankOrderJob` / `FinalizeRaiAcceptOrderJob` place or update orders and payments ([jobs.md](jobs.md)).

### [shipping](../shipping/)

- `ShippingMethod` on show, shipment update, assignments.
- `ShippingDriverManager` + optional `submitAssignment` (implemented by `BekiPostApiDriver`).
- Checkout copies carrier snapshot onto `order_shipments` at place.

### [tax](../tax/)

- Place-time `TaxService::calculate()` writes `tax_total`, `prices_include_tax`, `tax_breakdown` onto the order (checkout).
- Credit notes reuse stored line tax / `meta.net_total` and remaining shipping tax; they do not re-call `TaxService`.

### [rental](../rental/)

- `RentalReservation` on show and `returnRental`.
- Cancel restock skips `OrderItem::isRental()` lines.
- Place confirms held reservations (checkout + rental availability).

### [external-catalog](../external-catalog/)

- `ExternalCatalogOrderSettings` (`product.external_order.mode` auto/manual).
- `OrderExportService::send()` / sync columns on `orders`.
- `SubmitExternalOrderJob` dispatched by `OrderObserver`.

### [mail](../../shared/README.md#mail)

- `MailService`, `MailPayloadBuilder`, from/admin/order notification settings.
- Mailables listed in [notifications.md](notifications.md).

### [sales-specialist](../sales-specialist/)

- `SalesSpecialistOrderService::placeForCustomer()` sets `source = sales_specialist` and `sales_specialist_id`.
- Specialist UI lists `ownedBySalesSpecialist`. Admin list shows specialist name. Dashboard `SalesStatisticsService` groups by specialist.

### Inventory / catalog (not in the user dep list; required by code)

- `ProductInventory` restock on cancel and optional credit-note restock.
- `ProductProjectionService::rebuild` after rental return (catalog-products).

### Shared domain / support

- `App\Domain\Pricing\Money`, `DiscountAllocator` (credit notes).
- `App\Http\Resources\Api\V1\FinancialTotalsResource`, `App\Support\MoneyFormat`.
- `barryvdh/laravel-dompdf` for invoices.
- `yajra/laravel-datatables-oracle` for admin indexes.
- Settings: `store.*` invoice/storefront identity; `notifications.*` (see [notifications.md](notifications.md)).

## Depends on this module

| Consumer | Use |
|---|---|
| Cart-checkout | Creates orders; cart `order_id` |
| Payments | Order + `OrderPayment` finalize |
| External-catalog | Reads order graph for Elkos payload |
| Mail | Order payload + invoice generate |
| Admin dashboard | `SalesStatisticsService` queries `orders` |
| Sales-specialist | Creates and lists specialist orders |
| Coupon usage | `coupon_usages.order_id` at place |
| Customization uploads | `order_id` at place |

## Channels / locales

Admin payment/shipping pickers use `forCurrentChannel()` (`SetCurrentChannel` web middleware). API `me/orders` does not filter by channel. Orders table has **no** `channel_id`.

## Explicitly not dependencies of order **admin** HTTP

- Catalog price reindex jobs
- Storefront product search/facets
- Laravel Notification classes
