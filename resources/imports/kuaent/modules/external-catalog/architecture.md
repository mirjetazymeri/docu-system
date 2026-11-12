# Architecture — External catalog

Two outbound integrations share `config/external_catalog.php`. They do not call each other.

```
                    config/external_catalog.php
                    ┌──────────┴──────────┐
                    │                     │
         Elkos Order HTTP          Next.js ISR ping
                    │                     │
         ExternalCatalogApiService   StorefrontRevalidator
                    │                     │
         OrderExportService          ping / pingAfterCommit / attempt
                    ▲                     ▲
         ┌──────────┴──────────┐         │
         │                     │         ├── ProductService::rebuildProjectionsForIds
SubmitExternalOrderJob   OrderController::sendExternal
         ▲                     │         ├── ProductObserver::deleted
         │                     │         ├── PromotionService::rebuildProductProjections
OrderObserver::created         │         ├── CatalogReindexDispatcher batch then (ping)
(auto, afterCommit)            │         └── SystemCacheService::revalidateStorefront (attempt)
                               │
                    ExternalCatalogOrderSettings
                    (SettingService, key product.external_order.mode)

RebuildProductProjectionsJob ──► ProductProjectionService::rebuildMany
(queued; not Elkos)
```

## Layers

| Layer | Path | Role |
|---|---|---|
| Config | `config/external_catalog.php` | Elkos URL pieces, timeout, order queue, storefront revalidate URL/secret/tags |
| Settings | `ExternalCatalogOrderSettings` | Reads `product.external_order.mode` per channel via `SettingService` |
| Export | `OrderExportService` | Builds payload, writes sync columns, calls API |
| HTTP (Elkos) | `ExternalCatalogApiService` | JSON POST, optional `X-API-KEY`, `->throw()` |
| HTTP (storefront) | `StorefrontRevalidator` | JSON POST `{tags}`, header `x-revalidate-secret`, never throws |
| Jobs | `app/Jobs/ExternalCatalog/` | Auto order submit; projection chunk rebuild |
| Command | `VerifyStorefrontRevalidate` | Print env presence; optional live ping |
| Callers (not owned) | `OrderObserver`, `OrderController`, catalog/promotion/system-cache | Dispatch or invoke |

No service provider bindings. Laravel auto-wires constructor deps. `StorefrontRevalidator` is static.

## Config (`config/external_catalog.php`)

| Config key | Env name | Default in config |
|---|---|---|
| `base_url` | `EXTERNAL_CATALOG_BASE_URL` | `https://api.elkos-b2b.com` |
| `service_path` | `EXTERNAL_CATALOG_SERVICE_PATH` | `/API_Elkos/Service1.svc` |
| `api_key` | `EXTERNAL_CATALOG_API_KEY` | none (empty) |
| `order_endpoint` | `EXTERNAL_CATALOG_ORDER_ENDPOINT` | `Order` |
| `timeout` | `EXTERNAL_CATALOG_TIMEOUT` | `60` (cast int; request uses `max(1, …)`) |
| `order_queue` | `EXTERNAL_CATALOG_ORDER_QUEUE`, else `EXTERNAL_CATALOG_IMPORT_QUEUE` | `imports` |
| `revalidate_url` | `STOREFRONT_REVALIDATE_URL` | none |
| `revalidate_secret` | `STOREFRONT_REVALIDATE_SECRET` | none |
| `revalidate_tags` | `STOREFRONT_REVALIDATE_TAGS` | comma-split, trimmed, empty tokens dropped. Default string: `catalog,products,facets,attributes,categories,settings,menus,pages,promotions` |

`.env.example` documents the three `STOREFRONT_REVALIDATE_*` names (commented) plus `STOREFRONT_URL`. It does **not** list any `EXTERNAL_CATALOG_*` names. `STOREFRONT_URL` is `config('app.storefront_url')`, not this config file.

`RebuildProductProjectionsJob` does **not** read `config/external_catalog.php`. It uses `config/catalog.php` (`job_tries`, `job_timeout`, `projection_queue`). Those keys fall back to env names `EXTERNAL_CATALOG_PROJECTION_QUEUE` and `EXTERNAL_CATALOG_PROJECTION_CHUNK` (chunk is used by dispatchers, not by the job constructor). See [jobs.md](jobs.md).

## Elkos URL construction

`ExternalCatalogApiService::buildEndpointUrl`:

1. If `order_endpoint` matches `^https?://` (case-insensitive), use it as the full URL.
2. Else require non-empty `base_url` or throw `RuntimeException('External catalog base URL is not configured.')`.
3. If `service_path` is empty after trim/slash strip: `{baseUrl}/{endpoint}`.
4. Else: `{baseUrl}/{servicePath}/{endpoint}`.

With defaults that is `https://api.elkos-b2b.com/API_Elkos/Service1.svc/Order`.

## Order sync columns

Migration `database/migrations/2026_08_04_124500_add_external_order_sync_to_orders_table.php` adds nullable columns on `orders` (table owned by [orders](../orders/README.md)):

| Column | Type |
|---|---|
| `external_order_sync_status` | string(32) |
| `external_order_synced_at` | timestamp |
| `external_order_sync_error` | text |
| `external_order_sync_response` | json |

`Order` fillable includes those four. Casts: `external_order_synced_at` datetime, `external_order_sync_response` array.

`Order::isExternalOrderSynced()` is `external_order_sync_status === 'synced'` (literal, not the PHP constant).

Status strings written by `OrderExportService`: `pending`, `synced`, `failed`.

On failure, `send()` sets status + error only. It does **not** clear `external_order_synced_at` or `external_order_sync_response`.

## Auto vs manual

`ExternalCatalogOrderSettings::KEY_MODE` = `product.external_order.mode`.

Schema in `config/settings.php` group `product` → `external_order` → `mode`: select `manual` \| `auto`, default `manual`, `public => false`.

`mode(?int $channelId)` uses `$channelId ?? (int) config('catalog.default_channel_id', 1)`. Values other than `auto`/`manual` (case-insensitive trim) become `manual`.

`OrderObserver::created` does **not** pass a channel id, so auto/manual for dispatch uses that default channel id, not an order `channel_id` (`orders` has no `channel_id`).

## Horizon

`SubmitExternalOrderJob` queue default `imports`. `config/horizon.php` `supervisor-import`: comment “External order export jobs.”, queue `['imports']`, connection `redis`. Default `maxProcesses` 1, `tries` 3, `timeout` 300. Production `maxProcesses` 6; local 2.

`RebuildProductProjectionsJob` default queue `projections` → `supervisor-projections` (same file; timeout 300 on the supervisor).

## Adjacent URL helper (not a ping)

`App\Support\StorefrontUrl::base()` may use the **origin** of `config('external_catalog.revalidate_url')` when channel domain and `STOREFRONT_URL` are empty. That is link generation for auth/mail, not ISR. Documented on [auth architecture](../auth/architecture.md).

## What is not here

- Inbound Elkos or storefront webhooks: none in this codebase.
- Retry/backoff on either HTTP client: none (`Http::retry` is unused in `app/Http` and this module).
- Elkos product/catalog import pipeline: **not determinable as implemented** — no importer class under `App\Services\ExternalCatalog`.
