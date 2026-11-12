# Tests — External catalog

Searched `tests/` for `ExternalCatalog`, `OrderExportService`, `StorefrontRevalidator`, `SubmitExternalOrderJob`, `RebuildProductProjectionsJob`, `VerifyStorefrontRevalidate`, `external_order_sync`.

## Dedicated tests

### `tests/Unit/ExternalCatalog/OrderExportServiceTest.php`

`Tests\Unit\ExternalCatalog\OrderExportServiceTest` extends `Tests\TestCase`. Uses in-memory `Order` / `OrderItem` with `setRelation`; does **not** persist, dispatch jobs, or call `send()`.

`ExternalCatalogApiService` is Mockery-mocked and never invoked.

| Method | Asserts |
|---|---|
| `it_expands_bundle_order_lines_into_child_product_numbers` | `buildTransactionDetails` on a bundle line (`meta.bundle`: TABLE qty 1, CHAIR qty 6) with order qty 2 plus a simple line. Result: TABLE `Quantity` `2` `Price` `200`; CHAIR `12` / `0`; SIMPLE1 `1` / `10` |
| `it_keeps_simple_lines_unchanged_when_no_bundle_meta` | Two SKUs with `meta` null stay as ProductNumber/Quantity/Price strings without expansion |

Does **not** cover: `send()`, HTTP, sync columns, `buildPayload` header fields, `buildNote`, `resolveBundleComponents` live `Product::isBundle()` path, `SubmitExternalOrderJob`, admin `sendExternal`, or revalidation.

## Not present

There are **no** tests for:

- `ExternalCatalogApiService`
- `StorefrontRevalidator`
- `ExternalCatalogOrderSettings`
- `SubmitExternalOrderJob`
- `RebuildProductProjectionsJob`
- `storefront:verify-revalidate`
- `OrderController::sendExternal`
- `OrderObserver` auto dispatch
- `SystemCacheService::revalidateStorefront`

Orders module docs point at this same unit file as external-catalog-owned ([orders tests](../orders/tests.md)).
