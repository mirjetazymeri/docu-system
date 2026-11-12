# External catalog

Elkos **order export/sync** and Next.js storefront **ISR revalidation**. This module owns the HTTP clients, payload builder, queue jobs, artisan diagnostic command, and the per-channel auto/manual setting reader.

This is **not** storefront order placement, admin order CRUD, product/projection domain logic, or the system-cache admin UI. Those call into this module.

There is **no** Elkos catalog-import client under `App\Services\ExternalCatalog`. Product columns `api_id` / `import_hash` live on catalog models; no importer in this namespace writes them.

## Purpose

- POST a `Transaction` JSON payload to the Elkos Order endpoint when an order should be exported.
- Persist sync status on `orders` (`pending` / `synced` / `failed`).
- Auto-dispatch `SubmitExternalOrderJob` from `OrderObserver` when setting `product.external_order.mode` is `auto`.
- Manual in-process send from admin `OrderController::sendExternal` when mode is `manual`.
- Ping the storefront `STOREFRONT_REVALIDATE_URL` with ISR tags so Next.js caches refresh after catalog/promotion/system-cache work.
- Rebuild product projections in queued chunks (`RebuildProductProjectionsJob`) by calling catalog `ProductProjectionService` — same namespace, not Elkos export.

## Owned vs not owned

| Owned here | Owned elsewhere |
|---|---|
| `config/external_catalog.php` | `Order` aggregate, admin order HTTP — [orders](../orders/README.md) |
| `App\Services\ExternalCatalog\ExternalCatalogApiService` | `ProductProjectionService` — [catalog-products](../catalog-products/README.md) |
| `App\Services\ExternalCatalog\OrderExportService` | `SettingService` + admin settings HTTP — [settings-system](../settings-system/) |
| `App\Services\ExternalCatalog\StorefrontRevalidator` | `SystemCacheController` / `SystemCacheService` (callers) — [settings-system](../settings-system/) |
| `App\Services\ExternalCatalog\ExternalCatalogOrderSettings` | `OrderObserver` (dispatches the job) — [orders](../orders/README.md) |
| `App\Jobs\ExternalCatalog\SubmitExternalOrderJob` | `products:reindex` command — catalog-products |
| `App\Jobs\ExternalCatalog\RebuildProductProjectionsJob` | Catalog-rule reindex dispatcher — [catalog-rules-pricing](../catalog-rules-pricing/README.md) |
| `App\Console\Commands\VerifyStorefrontRevalidate` (`storefront:verify-revalidate`) | Next.js `/api/revalidate` implementation — not in this repository |
| Unit tests under `tests/Unit/ExternalCatalog/` | |

This module has **no** controllers, FormRequests, routes, events, or Eloquent models of its own.

## Docs in this folder

| File | Contents |
|---|---|
| [architecture.md](architecture.md) | Runtime shape, config, sync columns |
| [services.md](services.md) | API client, exporter, settings reader, revalidator |
| [jobs.md](jobs.md) | `SubmitExternalOrderJob`, `RebuildProductProjectionsJob` |
| [commands.md](commands.md) | `storefront:verify-revalidate` |
| [workflows.md](workflows.md) | Order export + storefront revalidate (Mermaid matching code) |
| [dependencies.md](dependencies.md) | Verified module deps |
| [tests.md](tests.md) | What exists |

## Hard rules from code (do not invent)

- Never document secret **values**. Env **names** only: `EXTERNAL_CATALOG_*`, `STOREFRONT_REVALIDATE_*`.
- Elkos response JSON schema beyond “must be a JSON array” is **not** specified in this repository.
- Auto export is **create-only** (`OrderObserver::created`). Updates do not dispatch `SubmitExternalOrderJob`.
- `SubmitExternalOrderJob::handle` returns without calling the exporter if mode is no longer auto, the order is missing, or `external_order_sync_status === synced`.
- `OrderExportService::send()` catches all `Throwable`s, writes `failed` on the order, and returns `{success: false}` — it does **not** rethrow. Laravel job `$tries = 3` therefore does **not** retry Elkos HTTP failures.
- Storefront pings never throw (`StorefrontRevalidator::ping` / `attempt`). Missing URL or secret → skipped, not an error throw.
- `php artisan products:reindex` queued Bus batches named `products:reindex` do **not** call `StorefrontRevalidator`. Catalog-rule projection batches named `catalog:projections` do (`StorefrontRevalidator::ping()` on batch `then`).
