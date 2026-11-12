# API — Storefront Addresses

Back to [README](README.md). Admin HTML CRUD is in [routes.md](routes.md).

File: `routes/api.php`  
Prefix: `/api` (Laravel default) + `v1`  
Group middleware: `channel.api`, `locale.api`  
Auth group: `auth:sanctum` then `verified.if-required`

Controller: `App\Http\Controllers\Api\V1\AddressController`  
No `check.permission`. The user may only read/write **their own** addresses (`$req->user()->addresses()`).

Cart `POST /api/v1/cart/addresses` is checkout cart data, **not** this resource.

---

## `GET /api/v1/me/addresses`

### Purpose

List the authenticated user’s addresses, newest id first.

### Authentication

Sanctum Bearer token. Email verification if `verified.if-required` requires it.

### Permissions

None beyond auth.

### Request

No query parameters read by this action.

### Response

`200` JSON:

```text
{ "data": [ <address>, ... ] }
```

Each address (`presentAddress()`):

| Key | Source |
| ----- | ------ |
| `id` | `user_addresses.id` |
| `label` | |
| `personal.first_name` / `last_name` / `phone` | |
| `company.name` | `company` column |
| `company.nui` | `nui` |
| `address.country_code` | uppercased |
| `address.country_name` | `countries.name` by code, else the code |
| `address.state_code` / `city` / `postcode` | |
| `address.address1` / `address2` | `address_line1` / `address_line2` |
| `defaults.billing` / `defaults.shipping` | booleans |
| `meta` | json/array |
| `created_at` / `updated_at` | ISO-8601 or null |

### Possible Errors

| Status | Condition |
| ------ | --------- |
| 401 | Unauthenticated |
| 403 | Unverified when verification is required (`verified.if-required`) |

### Side Effects

None.

---

## `POST /api/v1/me/addresses`

### Purpose

Create an address for the current user.

### Request body

Either flat fields or grouped (`personal`, `company`, `address`, `defaults`). `mergeGroupedPayload()` flattens before validation. `country_code` is trimmed and uppercased.

Validation: [requests-validation.md](requests-validation.md) (API section). Field set depends on `CheckoutAddressFields` / checkout settings.

### Response

`201` `{ "data": <address> }` as above.

### Possible Errors

| Status | Condition |
| ------ | --------- |
| 401 / 403 | Auth / verification |
| 422 | Validation (including `prohibited` hidden fields, unknown `country_code`) |

### Side Effects

Insert `user_addresses`. If default flags are set, other rows (including soft-deleted) have those flags cleared. Unlike `UserAddressService`, this path does **not** auto-promote a default when both flags are false.

---

## `PUT /api/v1/me/addresses/{id}`

### Purpose

Update an address owned by the current user.

### Route parameters

| Param | Rules |
| ----- | ----- |
| `id` | Integer; `findOrFail` on `$user->addresses()` |

### Response

`200` `{ "data": <address> }`

### Possible Errors

| Status | Condition |
| ------ | --------- |
| 404 | Id not found **or** belongs to another user |
| 422 | Validation |

### Side Effects

Update row + default-flag clearing as on store.

---

## `DELETE /api/v1/me/addresses/{id}`

### Purpose

Soft-delete the address.

### Response

`204` empty body.

### Side Effects

Soft delete only. **Does not** call `UserAddressService::delete`, so remaining addresses are not promoted to default. Unique generated columns may still hold the user id on the trashed row until another write clears flags via `withTrashed()`.

---

## `POST /api/v1/me/addresses/{id}/default`

### Purpose

Mark an existing address as default shipping, billing, or both.

### Request

```text
{ "type": "shipping" | "billing" | "both" }
```

### Response

`200` `{ "data": <address> }`

### Side Effects

Transaction: set the chosen flag(s) false for **all** of the user’s addresses (default SoftDeletes scope), then set true on the target and `save()`.

---

## Related API (auth module)

`POST /api/v1/auth/register` creates a `User`, assigns Spatie role `Customer`, and attaches user group slug `customer`. Documented in the auth module; it is the main storefront writer of this module’s identity tables.
