# Rental — routes

## Storefront API

`bootstrap/app.php` registers `routes/api.php` with Laravel’s `/api` prefix.

Group in `routes/api.php`: `prefix v1` + middleware `channel.api`, `locale.api`.

| Method | URI | Controller | Auth |
|---|---|---|---|
| GET | `/api/v1/products/{id}/rental-availability` | `ProductRentalAvailabilityController@show` | None |

`{id}` is type-hinted `int`. The route does **not** use `whereNumber`.

Cart rental add/update/remove/clear and checkout place-order are [cart-checkout routes](../cart-checkout/routes.md) (`POST /api/v1/cart/items`, etc.). They are not named rental routes.

## Admin web

Group in `routes/web.php`:

```
middleware: auth, verified.if-required, set.user.locale, role:Admin
prefix: admin
name: admin.
```

Inside `orders` prefix (`name: orders.`):

| Method | URI | Name | Controller |
|---|---|---|---|
| POST | `/admin/orders/{order}/rentals/{reservation}/return` | `admin.orders.rentals.return` | `OrderController@returnRental` |

Route-model binding: `{order}` → `Order`, `{reservation}` → `RentalReservation`. Controller still asserts `reservation.order_id === order.id`.

Permission middleware on the controller: `order_edit`.

Product admin create/update (rental tab) is [catalog-products routes](../catalog-products/routes.md).

## Console

`routes/console.php` (loaded as the commands file):

| Command | Schedule |
|---|---|
| `rentals:release-expired-holds` | `everyTenMinutes()` |

See [commands.md](commands.md).
