# Sales specialist — routes

Source: `routes/web.php`. No entries in `routes/api.php` or `routes/console.php`.

## Middleware stack

Group:

```
Route::middleware(['auth', 'verified.if-required', 'set.user.locale', 'role:Sales Specialist'])
    ->prefix('sales-specialist')
    ->name('sales-specialist.')
```

| Alias | Class |
|---|---|
| `auth` | Laravel session auth (unauthenticated → login) |
| `verified.if-required` | `App\Http\Middleware\EnsureEmailIsVerifiedIfRequired` |
| `set.user.locale` | `App\Http\Middleware\SetUserLocale` |
| `role:Sales Specialist` | `App\Http\Middleware\RoleMiddleware` (`hasAnyRole('Sales Specialist')` or 403 `Unauthorized access.`) |

Also on the `web` group: `App\Http\Middleware\SetCurrentChannel` (`bootstrap/app.php`). CSRF applies (preview/store JS sends `X-CSRF-TOKEN`).

Aliases are registered in `bootstrap/app.php`.

## Route table

Prefix URL: `/sales-specialist`. Name prefix: `sales-specialist.`.

| Method | Path | Name | Controller@action | FormRequest |
|---|---|---|---|---|
| GET | `/sales-specialist/dashboard` | `sales-specialist.dashboard` | `DashboardController@index` | — |
| GET | `/sales-specialist/customers/search` | `sales-specialist.customers.search` | `CustomerController@search` | — |
| POST | `/sales-specialist/customers` | `sales-specialist.customers.store` | `CustomerController@store` | `StoreCustomerRequest` |
| GET | `/sales-specialist/products/search` | `sales-specialist.products.search` | `OrderController@searchProducts` | — |
| POST | `/sales-specialist/orders/cart-preview` | `sales-specialist.orders.cart-preview` | `OrderController@cartPreview` | `PreviewCartRequest` |
| POST | `/sales-specialist/orders/fulfillment-options` | `sales-specialist.orders.fulfillment-options` | `OrderController@fulfillmentOptions` | `PreviewFulfillmentRequest` |
| GET | `/sales-specialist/orders` | `sales-specialist.orders.index` | `OrderController@index` | — |
| GET | `/sales-specialist/orders/create` | `sales-specialist.orders.create` | `OrderController@create` | — |
| POST | `/sales-specialist/orders` | `sales-specialist.orders.store` | `OrderController@store` | `StoreOrderRequest` |
| GET | `/sales-specialist/orders/{order}` | `sales-specialist.orders.show` | `OrderController@show` | — |

Resource registration:

```
Route::resource('orders', SalesSpecialistOrderController::class)->only(['index', 'create', 'store', 'show']);
```

There is no `edit`, `update`, or `destroy`.

Preview and customer-store JSON posts are declared **before** the resource so they are not captured as `{order}`.

## Adjacent web routes (not this prefix)

| Route | Relevance |
|---|---|
| `GET /` | If `hasRole('Sales Specialist')` → redirect `sales-specialist.dashboard` (after Admin check, before Customer). |
| Fortify `LoginResponse` | Customer first, then Sales Specialist → `sales-specialist.dashboard`, else `admin.dashboard`. |
| `profile.edit` / `profile.update` / `profile.password` | `auth` + `verified.if-required` + `set.user.locale` — **no** role middleware. Header profile link works for specialists. |

## Unrouted controller methods

| Method | Notes |
|---|---|
| `OrderController::searchShippingMethods` | No `Route::` match in `web.php` or `api.php`. |
| `OrderController::searchPaymentMethods` | Same. |

Create-form product search uses public catalog API routes (`GET /api/v1/products/search`, `GET /api/v1/products-projection`), documented under [`../catalog-products/routes.md`](../catalog-products/routes.md). Country/city: `GET /api/v1/countries`, `GET /api/v1/countries/{code}/cities`.
