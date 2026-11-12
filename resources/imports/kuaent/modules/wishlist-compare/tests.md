# Tests

Back to [README](README.md).

## Search performed

Searched `tests/` (all PHP files) for `wishlist`, `Wishlist`, `compare`, and `Compare` (case-insensitive). **No matches.**

Also inspected:

| Path | Relation to this module |
| ---- | ---------------------- |
| `tests/Feature/Configurable/ConfigurableProductTest.php` | Configurable **product type**, not compare lists |
| `tests/Feature/Configurable/ConfigurableCartTest.php` | Cart behavior for configurable products |
| `tests/Feature/Configurable/ConfigurableTestCase.php` | Shared fixture for the above |
| `tests/Feature/Bundle/*` | Bundle catalog/cart |
| `tests/Feature/ExampleTest.php` | Default Laravel example |
| `database/factories` | No Wishlist/Compare factories |

`tests/Feature/Configurable/` was called out in the documentation inventory as a possible place for compare tests. Those files do not mention compare lists, `CompareController`, `X-Compare-Token`, or `compare_items`.

## Unit tests

None for `WishlistController`, `CompareController`, models, or resources.

## Feature / API tests

None hitting `/api/v1/wishlist` or `/api/v1/compare`.

## Important scenarios apparently not covered in `tests/`

Because the search found zero tests, the following behaviors from the controllers have no PHPUnit coverage in this repository:

- Guest create/show with `X-Wishlist-Token` / `X-Compare-Token` and query-string tokens
- UUID generation when the guest token is omitted
- Sanctum user list vs guest list
- Guest-to-user merge (`attachGuestWishlistToUser` / `attachGuestCompareToUser`)
- `firstOrCreate` duplicate product
- `exists:products,id` 422
- `clear` no-op vs clear-with-items
- `abort(400, 'Channel not resolved')`
- Item remove by item id vs product id
- Resource translation/price/image mapping
- `session_token` unique constraint vs per-channel `firstOrCreate` keys

## Other artifacts (not PHPUnit)

`postman_collection.json` contains Wishlist and Compare request folders with scripts that assert `pm.response.code` is below 500 and store tokens/item ids. That is a manual/Postman collection, not part of `tests/`.
