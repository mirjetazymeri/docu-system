# Routes

## Admin web (`routes/web.php`)

Group: `middleware(['auth', 'verified.if-required', 'set.user.locale', 'role:Admin'])`, prefix `admin`, name `admin.`.

### Shipping groups

| Method | Path | Name | Controller |
|---|---|---|---|
| GET | `admin/shipping-groups` | `admin.shipping_groups.index` | `ShippingGroupController@index` |
| GET | `admin/shipping-groups/edit/{id}` | `admin.shipping_groups.edit` | `edit` |
| PUT | `admin/shipping-groups/{id}` | `admin.shipping_groups.update` | `update` |
| POST | `admin/shipping-groups/apply-suggested` | `admin.shipping_groups.apply_suggested` | `applySuggested` |

### Shipping methods

| Method | Path | Name | Controller |
|---|---|---|---|
| GET | `admin/shipping-methods` | `admin.shipping_methods.index` | `ShippingMethodController@index` |
| GET | `admin/shipping-methods/create` | `admin.shipping_methods.create` | `create` |
| POST | `admin/shipping-methods` | `admin.shipping_methods.store` | `store` |
| GET | `admin/shipping-methods/edit/{id}` | `admin.shipping_methods.edit` | `edit` |
| PUT | `admin/shipping-methods/{id}` | `admin.shipping_methods.update` | `update` |
| DELETE | `admin/shipping-methods/{id}` | `admin.shipping_methods.delete` | `delete` |

### Lookup (orders UI)

| Method | Path | Name |
|---|---|---|
| GET | `admin/lookups/shipping-methods` | `admin.lookups.shipping-methods` |

Country routes (`admin/countries…`) are geo-countries; they carry `shipping_group_id` and city BEKI IDs.

## Storefront API (`routes/api.php`)

Prefix `v1`, middleware `channel.api`, `locale.api`. Full paths under `/api/v1`.

| Method | Path | Controller | Purpose |
|---|---|---|---|
| GET | `cart/shipping/quotes` | `CartController@shippingQuotes` | List usable quotes |
| POST | `cart/shipping/method` | `CartController@setShippingMethod` | Persist selected method + amount |

These sit next to other cart routes (`POST cart/addresses` should run first so destination country exists). There is no dedicated `/shipping/methods` catalog endpoint; quotes **are** the catalog.

`GET countries` / `GET countries/{code}/cities` are geo-countries (no shipping group in payload).

## Sales specialist web (consumer)

Prefix `sales-specialist`, role `Sales Specialist`:

- `POST sales-specialist/orders/fulfillment-options` → JSON `shipping_quotes` from `CartService::shippingQuotes`
- Order `store` applies `shipping_method_id` via `setShippingMethod`

Not shipping-owned routes.

## Not present

No `POST /api/v1/webhooks/shipping/{carrier}/status`. No admin JSON API for carrier credentials. See [`docs/shipping-integration-pending/PENDING_TASKS.md`](../../shipping-integration-pending/PENDING_TASKS.md).
