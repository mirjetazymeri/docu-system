# Payments routes

## Admin (`routes/web.php`)

Group: `middleware(['auth', 'verified.if-required', 'set.user.locale', 'role:Admin'])`, `prefix('admin')`, `name('admin.')`.

Controller permission middleware is additional (`payment_method_*`).

| Method | Path | Name | Controller |
| --- | --- | --- | --- |
| GET | `/admin/payment-methods` | `admin.payment_methods.index` | `index` |
| GET | `/admin/payment-methods/create` | `admin.payment_methods.create` | `create` |
| POST | `/admin/payment-methods` | `admin.payment_methods.store` | `store` |
| GET | `/admin/payment-methods/edit/{id}` | `admin.payment_methods.edit` | `edit` |
| PUT | `/admin/payment-methods/{id}` | `admin.payment_methods.update` | `update` |
| DELETE | `/admin/payment-methods/{id}` | `admin.payment_methods.delete` | `delete` |

Sidebar: `resources/views/layouts/components/main-sidebar.blade.php` (`payment_method_view` / `payment_method_create`).

## API (`routes/api.php`)

Laravel `withRouting(api: …)` prefixes `/api`. Group: `prefix('v1')`, `middleware(['channel.api', 'locale.api'])`.

No `auth:sanctum` on these payment routes.

| Method | Path | Controller |
| --- | --- | --- |
| GET | `/api/v1/payment/methods` | `Api\V1\PaymentMethodController@index` |
| POST | `/api/v1/cart/payment/method` | `Api\V1\CartController@setPaymentMethod` |
| GET, POST | `/api/v1/payments/{gateway}/return` | `PaymentCallbackController@handleReturn` |
| POST | `/api/v1/payments/{gateway}/capture-browser-result` | `captureBrowserResult` |
| GET, POST | `/api/v1/payments/{gateway}/notify` | `handleNotify` |
| GET | `/api/v1/payments/{gateway}/status` | `status` |

`{gateway}` is the driver alias: `raiaccept` or `pcbbank` for handlers; `cod` has no callback handler.

Related checkout route (not a payment-module route, used for capture vs place): `POST /api/v1/checkout/place-order` (`throttle:20,1`).

## Orders admin (payment rows, not method CRUD)

Under `/admin/orders`: `POST …/{order}/mark-paid`, `PATCH …/{order}/payments/{payment}`. See [controllers.md](controllers.md).
