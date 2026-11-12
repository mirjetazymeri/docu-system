# Rental — storefront API

Base: `/api/v1`. Middleware: `channel.api`, `locale.api`. Channel from `?channel=` / `X-Channel` / default (`ResolveApiChannel`). Missing channel on availability → HTTP **400** `Channel not resolved`.

Cart/checkout JSON that carries rental fields is owned by [cart-checkout](../cart-checkout/api.md). Order JSON: [orders](../orders/api.md). Repeated here only for rental keys.

---

## `GET /api/v1/products/{id}/rental-availability`

Public. `{id}` is numeric product id (`findOrFail`).

### Query

| Param | Rules | Default |
|---|---|---|
| `start` | required date | |
| `end` | required date, `after_or_equal:start` | |
| `qty` | optional integer 1–9999 | `1` |

### Success `200`

```json
{
  "data": {
    "product_id": 1,
    "available_units": 3,
    "is_available": true,
    "requested_qty": 1,
    "quote": {
      "period_unit": "day",
      "periods": 7,
      "rate": 25.0,
      "unit_price": 175.0,
      "line_total": 175.0,
      "deposit": 150.0,
      "currency": "EUR",
      "starts_on": "2026-09-01",
      "ends_on": "2026-09-07",
      "end_date_inclusive": true
    }
  }
}
```

`is_available` is `available_units >= requested_qty`. `available_units` does **not** exclude the caller’s cart holds (no cart token is read).

`quote` always runs when the product is rentable and a channel rate exists. Quote math: [services.md](services.md).

### Errors

| Status | When |
|---|---|
| 400 | Channel not resolved |
| 404 | Product missing |
| 422 | Validation (`start`/`end`/`qty`); product not rentable (`errors.product`); quote/range (`errors.rental` — min/max period, no rate on channel, end before start) |

Not rentable message: `This product is not available to rent.`

Postman: collection request **Get Rental Availability** (`postman_collection.json`).

---

## Catalog projection (catalog-products)

PLP `ProductProjectionResource` and PDP `ProductProjectionDetailResource` expose:

- `sale_mode` from payload (default `sale`)
- `rental` from payload (`ProductPayloadBuilder::buildRental`) or `null`

Rental block when present: `period_unit`, `rate`, `deposit`, `min_period`, `max_period`, `currency`, `end_date_inclusive`.

Date-range stock is **not** in the projection. Use this availability endpoint.

---

## Cart (cart-checkout)

`POST /api/v1/cart/items` with `line_type: rental` plus `rental_start_date`, `rental_end_date`. Other cart endpoints do not accept new dates (qty/remove/clear only).

`CartItemResource` when `line_type === 'rental'`:

| Key | Source |
|---|---|
| `line_type` | `rental` |
| `rental.starts_on` / `ends_on` | dates |
| `rental.periods` | int |
| `rental.period_unit` | string |
| `rental.deposit` | float (`deposit_total`) |

Otherwise `rental` is `null`. `unit_price` / `row_total` are the rental quote snapshots.

`FinancialTotalsResource`: `deposit` and `deposit_total` (decimal strings). Included in `grand_total`.

`DELETE /api/v1/cart`: cancels **held** reservations for that cart.

---

## Order (place-order + my orders)

`OrderResource` items include `line_type`, `deposit_total`, `rental_starts_on`, `rental_ends_on`, `rental_periods`, `rental_period_unit`. Header `deposit_total` / nested `totals.deposit_total`.

There is **no** storefront API to mark a rental returned. That is admin `POST /admin/orders/{order}/rentals/{reservation}/return` only.
