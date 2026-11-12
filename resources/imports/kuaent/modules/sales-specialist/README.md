# Sales specialist

Staff sales desk: create **Customer** accounts and place **channel-scoped** orders by reusing checkout cart/place services.

This module does **not** own the `Order` or `Cart` aggregates. Placement calls [`../cart-checkout/README.md`](../cart-checkout/README.md) (`CartService`, `OrderService::place()`). After an order exists, admin lifecycle (status, shipments, invoices, credit notes) belongs to [`../orders/README.md`](../orders/README.md).

## Purpose

- Give users with Spatie role **`Sales Specialist`** a Blade UI under `/sales-specialist/*`.
- Search and create storefront **Customer** users (role `Customer`, group slug `customer`).
- Build a scratch cart for the specialist’s current channel, quote shipping/payment the same way storefront checkout does, and place the order.
- List and show **only** orders where `orders.sales_specialist_id` is the logged-in user.

## Owned vs not owned

| Owned here | Owned elsewhere |
|---|---|
| `app/Http/Controllers/SalesSpecialist/*` | `Order` model, status machine, admin order ops → [`../orders/README.md`](../orders/README.md) |
| `app/Services/SalesSpecialist/SalesSpecialistOrderService.php` | `CartService`, `OrderService::place()` → [`../cart-checkout/README.md`](../cart-checkout/README.md) |
| `app/Http/Requests/SalesSpecialist/*` | Product catalog CRUD / projection index → [`../catalog-products/README.md`](../catalog-products/README.md) |
| Blade: `resources/views/sales-specialist/*` | Payment drivers / redirect gateways → [`../payments/README.md`](../payments/README.md) |
| Routes `sales-specialist.*` in `routes/web.php` | Shipping quote drivers → [`../shipping/README.md`](../shipping/README.md) |
| Ownership check `sales_specialist_id === auth id` | Channel bind (`SetCurrentChannel`, `Channel`) → [`../channels-locales/README.md`](../channels-locales/README.md) |
| Sets `orders.source = sales_specialist` and `sales_specialist_id` at place | User/role/group records → [`../users-access-control/README.md`](../users-access-control/README.md) |
| | Session login, Fortify redirect, `UserObserver` welcome mail → [`../auth/README.md`](../auth/README.md) |
| | Tax quote at preview/place → [`../tax/README.md`](../tax/README.md) |

There is **no** REST API for this desk. Storefront `POST /api/v1/checkout/place-order` is a different entry point.

## Docs in this folder

| File | Contents |
|---|---|
| [architecture.md](architecture.md) | Scratch cart, channel bind, source attribution, UI |
| [controllers.md](controllers.md) | Dashboard, Customer, Order, `ResolvesSalesChannel` |
| [services.md](services.md) | `SalesSpecialistOrderService` and checkout call-outs |
| [requests-validation.md](requests-validation.md) | FormRequests + `ValidatesSalesCustomer` |
| [routes.md](routes.md) | `sales-specialist.*` table |
| [permissions.md](permissions.md) | Role middleware, no named permissions, ownership |
| [workflows.md](workflows.md) | Create customer / create order (Mermaid = actual calls) |
| [dependencies.md](dependencies.md) | Verified module deps |
| [tests.md](tests.md) | What `tests/` covers (none for this module) |

## Hard rules from code (do not invent)

- **Checkout does not set `orders.source`.** `CheckoutController::placeOrder()` calls `OrderService::place()` with `customer_note`, `accept_terms`, `idempotency_key`, and `email` only. Payment finalize jobs pass `idempotency_key` only. `OrderService` writes `'source' => $opts['source'] ?? null`.
- **Only this module’s placement sets specialist attribution.** `SalesSpecialistOrderService::placeForCustomer()` is the only application caller that passes `sales_specialist_id` and `source` (`Order::SOURCE_SALES_SPECIALIST` = `sales_specialist`).
- **Area gate is the Spatie role name `Sales Specialist`**, not access-level permissions. Routes use `role:Sales Specialist` (`App\Http\Middleware\RoleMiddleware`). There is no `check.permission` on this prefix.
- **List/show scope is `sales_specialist_id`**, not channel and not `user_id`. Another specialist’s order → HTTP 403 on `show`.
- **Redirect payment drivers are excluded** from fulfillment preview (`RaiAcceptDriver`, `PcbBankDriver`). Place goes through `CartService::setPaymentMethod` + `OrderService::place()` (COD-style, immediate place).
- **Scratch cart token** is `sales-specialist:{authId}` (one active cart per specialist, wiped and rebuilt on each preview/place).
- **New-cart currency is hardcoded `EUR`** in `prepareCart()`. Channel has no currency column used here.
- Never document env secrets or seeded plaintext passwords.

## Related modules

| Module | Why |
|---|---|
| [auth](../auth/README.md) | `auth` + `verified.if-required`; Fortify login redirect; `UserObserver` on customer create |
| [users-access-control](../users-access-control/README.md) | `User`, Spatie `Role`, `UserGroup`, `hasRole('Customer')` |
| [catalog-products](../catalog-products/README.md) | `Product::forChannel` + `status=active`; create form also calls public catalog API |
| [cart-checkout](../cart-checkout/README.md) | Scratch cart + `OrderService::place()` |
| [orders](../orders/README.md) | `Order` aggregate after place; show reuses some admin Blade partials |
| [channels-locales](../channels-locales/README.md) | `app('currentChannel')`, `users.current_channel_id`, `User::channels()` |
| [payments](../payments/README.md) | `PaymentMethod`, `PaymentDriverManager`, `RedirectPaymentDriver` |
| [shipping](../shipping/README.md) | `ShippingMethod`; quotes via `CartService::shippingQuotes()` |
| [tax](../tax/README.md) | `TaxService::calculate` on cart preview (constructor injection) |
