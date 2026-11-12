# Requests and validation

Back to [README](README.md). API: [api.md](api.md).

## FormRequest classes

None. There are no `app/Http/Requests` classes named for wishlist or compare. A glob of `app/Http/Requests` for Wishlist/Compare names returns no files.

Authorization for these endpoints is not implemented via FormRequest `authorize()` or policies. There is no `WishlistPolicy` / `ComparePolicy`.

## Inline controller validation

Both add-item actions call `$request->validate([...])` on `Illuminate\Http\Request`.

### `WishlistController::addItem()`

Source: `app/Http/Controllers/Api/V1/WishlistController.php`

| Field | Rules | Notes |
| ----- | ----- | ----- |
| `product_id` | `required`, `integer`, `exists:products,id` | Database `exists` rule as written. `Product` uses `SoftDeletes`; this rule does not add `whereNull('deleted_at')` in the controller. |

No `prepareForValidation` / after-hooks (not a FormRequest).

### `CompareController::addItem()`

Source: `app/Http/Controllers/Api/V1/CompareController.php`

| Field | Rules | Notes |
| ----- | ----- | ----- |
| `product_id` | `required`, `integer`, `exists:products,id` | Same as wishlist. |

## Unvalidated inputs that still change behavior

These are not FormRequest fields. They are read directly:

| Input | Used by | Effect |
| ----- | ------- | ------ |
| Header `X-Wishlist-Token` | `WishlistController::extractToken()` | Guest list identity |
| Query `wishlist_token` | same, if header empty | Guest list identity |
| Header `X-Compare-Token` | `CompareController::extractToken()` | Guest list identity |
| Query `compare_token` | same, if header empty | Guest list identity |
| Sanctum user | `userId()` | Switches to user list + merge |
| Route `{id}` on remove | `removeItem` | Item primary key; not validated with `exists:` |
| Channel / locale headers | group middleware | See [routes.md](routes.md) |

`create`, `show`, `clear`, and `removeItem` do not call `$request->validate()`.

## Unique constraint vs application rules

Item tables unique `(list_id, product_id)` plus `firstOrCreate` on those two columns. The application does not return a dedicated "already in list" error; a duplicate add is a no-op insert.

`session_token` is a unique uuid column. Controllers do not validate token format beyond using the string from the header/query (or `Str::uuid()`).

## Custom validation rules / DTOs

None in this module.
