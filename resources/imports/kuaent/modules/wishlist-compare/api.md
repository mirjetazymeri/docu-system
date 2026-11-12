# API

Back to [README](README.md). Controllers: [controllers.md](controllers.md). Validation: [requests-validation.md](requests-validation.md).

Prefix: `/api/v1`. Group middleware: `channel.api`, `locale.api`. Optional Sanctum Bearer token is read in the controller; it is not required by route middleware.

Resource responses use Laravel's default `data` wrap (no `JsonResource::withoutWrapping()` in `AppServiceProvider`). `create` / `show` add top-level `meta`. Exact HTTP status codes for resource responses are Laravel's default for `JsonResource` (controllers do not call `response()->json(..., 201)`).

Shared channel headers (resolved by `ResolveApiChannel`, not unique to this module): query `channel` or header `X-Channel` (channel **code**). Fallback in middleware is channel `code = default`.

Shared locale inputs (resolved by `SetLocaleFromRequest`): query `locale`, header `X-Locale`, or `Accept-Language`. These resources do not select translations by that locale (see [controllers.md](controllers.md)).

No sample JSON is invented beyond fields returned by the resource `toArray()` methods and the explicit `clear` JSON arrays.

---

## `POST /api/v1/wishlist`

### Purpose

Create or attach the active wishlist; return `meta.wishlist_token`.

### Authentication

Optional Sanctum (`$request->user('sanctum')`). Guest token optional; if absent a UUID is generated.

### Permissions

None.

### Request

| Location | Name | Role |
| -------- | ---- | ---- |
| Header | `X-Wishlist-Token` | Guest list token |
| Query | `wishlist_token` | Same, if header empty |
| Header | `Authorization` | Optional `Bearer` Sanctum token |
| Body | — | Unused |

### Validation

None.

### Response

`WishlistResource` plus `meta.wishlist_token` = stored `session_token` or the token used for this request.

### Possible Errors

| Status | Condition |
| ------ | --------- |
| 400 | `Channel not resolved` (`currentChannel` has no `id`) |

### Side Effects

May insert `wishlists` / merge guest into user (see [controllers.md](controllers.md)). No events or jobs.

---

## `GET /api/v1/wishlist`

### Purpose

Fetch the active wishlist; creates one if the same path as `create()` would.

### Authentication

Optional Sanctum. Guest token optional (missing token generates a UUID and creates a guest list).

### Permissions

None.

### Request

Same headers/query as `POST /wishlist`.

### Validation

None.

### Response

`WishlistResource` plus `meta.wishlist_token` = `$wishlist->session_token` only (null when the user list was created with a null token).

### Possible Errors

| Status | Condition |
| ------ | --------- |
| 400 | Channel not resolved |

### Side Effects

Same create/merge behavior as `create()`.

---

## `DELETE /api/v1/wishlist`

### Purpose

Delete all items on the active list. Does not delete the header row. Does not create a list.

### Authentication

Optional Sanctum. Guest requires a token to find a list; without a token the handler returns the no-op success payload.

### Permissions

None.

### Request

Token header/query as above (guest). No body.

### Validation

None.

### Response

```text
{ "success": true, "message": "No active wishlist to clear." }
```

or

```text
{ "success": true, "message": "Wishlist cleared." }
```

### Possible Errors

| Status | Condition |
| ------ | --------- |
| 400 | Channel not resolved (if `findActiveWishlist` reaches a query that calls `channelId()`; guest-without-token returns before some lookups — user path always calls `latestActiveUserWishlist` → `channelId()`) |

### Side Effects

Deletes `wishlist_items` for that list; sets `items_count = 0`, `status = 'active'`.

---

## `POST /api/v1/wishlist/items`

### Purpose

Add a product (`firstOrCreate` on `wishlist_id` + `product_id`). Creates/attaches the list first via `getWishlist()`.

### Authentication

Optional Sanctum and/or wishlist token.

### Permissions

None.

### Request

JSON body:

| Field | Rules | Description |
| ----- | ----- | ----------- |
| `product_id` | `required`, `integer`, `exists:products,id` | Catalog product id |

### Response

`WishlistResource` (no `meta` additional).

### Possible Errors

| Status | Condition |
| ------ | --------- |
| 400 | Channel not resolved |
| 422 | `product_id` validation failure (Laravel default validation JSON) |

### Side Effects

May insert `wishlists` and/or `wishlist_items`; updates `items_count`.

---

## `DELETE /api/v1/wishlist/items/{id}`

### Purpose

Remove one wishlist line by **item id**.

### Authentication

Optional Sanctum and/or wishlist token (`getWishlist()`, which creates a list if needed).

### Permissions

None.

### Request

Route parameter `id` = `wishlist_items.id`. Missing item is not a dedicated error (delete affecting 0 rows still returns the list).

### Response

`WishlistResource` (no `meta`).

### Possible Errors

| Status | Condition |
| ------ | --------- |
| 400 | Channel not resolved |

### Side Effects

May delete one `wishlist_items` row; updates `items_count`. May create a list if none existed.

---

## Wishlist `data` item shape (`WishlistResource`)

| Field | Source |
| ----- | ------ |
| `id` | Wishlist / item id |
| `items_count` | Cast to int |
| `items[].id` | `wishlist_items.id` |
| `items[].product_id` | `wishlist_items.product_id` |
| `items[].sku` | `product.sku` or `''` |
| `items[].name` / `slug` | First translation, else product attribute, else `''` |
| `items[].unit_price` / `final_price` | `effectivePrice()` or channel `price` |
| `items[].price` | Channel `ProductPrice->price` or `product.price` attribute or `0` |
| `items[].image` / `image_url` | Same resolved path/url or `null` |
| `created_at` / `updated_at` | ISO-8601 or null |

---

## `POST /api/v1/compare`

### Purpose

Create or attach the active compare list; return `meta.compare_token`.

### Authentication

Optional Sanctum. Guest token optional; UUID generated if missing.

### Permissions

None.

### Request

| Location | Name | Role |
| -------- | ---- | ---- |
| Header | `X-Compare-Token` | Guest list token |
| Query | `compare_token` | Same, if header empty |
| Header | `Authorization` | Optional Sanctum Bearer |

### Response

`CompareResource` plus `meta.compare_token` = `session_token ?: $token`.

### Possible Errors

| Status | Condition |
| ------ | --------- |
| 400 | Channel not resolved |

### Side Effects

May insert `compares` / merge guest into user.

---

## `GET /api/v1/compare`

### Purpose

Fetch (or create via `getCompare()`) the compare list.

### Authentication

Optional Sanctum.

### Response

`CompareResource` plus `meta.compare_token` = `$compare->session_token` only.

### Possible Errors

| Status | Condition |
| ------ | --------- |
| 400 | Channel not resolved |

---

## `DELETE /api/v1/compare`

### Purpose

Remove all compare items. Header row remains. Guest without token: no-op success.

### Response

```text
{ "success": true, "message": "No active compare to clear." }
```

or

```text
{ "success": true, "message": "Compare cleared." }
```

### Side Effects

Deletes `compare_items`; sets `items_count = 0`. Does not update `status`.

---

## `POST /api/v1/compare/items`

### Purpose

Add a product (`firstOrCreate` on `compare_id` + `product_id`).

### Validation

| Field | Rules |
| ----- | ----- |
| `product_id` | `required`, `integer`, `exists:products,id` |

### Response

`CompareResource` (no `meta`).

### Possible Errors

| Status | Condition |
| ------ | --------- |
| 400 | Channel not resolved |
| 422 | Validation failure |

---

## `DELETE /api/v1/compare/items/{id}`

### Purpose

Delete compare line by **item id**. Missing id is not a dedicated 404.

### Response

`CompareResource` (no `meta`).

---

## Compare `data` item shape (`CompareResource`) extras vs wishlist

| Field | Source |
| ----- | ------ |
| `items[].product_id` | `$product?->id` |
| `items[].type` | `product.type` or `''` |
| `items[].short_description` / `description` | First translation or `''` |
| `items[].images[]` | `url`, `alt`, `is_primary` per image |
| `items[].inventory.is_in_stock` | `data_get($product, 'inventory.is_in_stock', true)` |
| `items[].rating.avg` | `data_get(..., 'rating.avg', 0)` |
| `items[].rating.count` | `data_get(..., 'rating.count', 0)` |

---

## Postman

`postman_collection.json` includes Wishlist and Compare folders that match these URIs and tokens. Collection tests only assert status `< 500` and persist `meta.*_token` / first item id when present. That collection is not an automated PHPUnit suite.
