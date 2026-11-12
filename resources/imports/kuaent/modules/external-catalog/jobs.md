# Jobs — External catalog

Both jobs: `Illuminate\Contracts\Queue\ShouldQueue`. Connection is **not** set on the job (Laravel default `QUEUE_CONNECTION`).

---

## `App\Jobs\ExternalCatalog\SubmitExternalOrderJob`

**File:** `app/Jobs/ExternalCatalog/SubmitExternalOrderJob.php`

```
SubmitExternalOrderJob::dispatch($order->id);
```

| Property | Value |
|---|---|
| Constructor | `public int $orderId`; `onQueue((string) config('external_catalog.order_queue', 'imports'))` |
| `$tries` | `3` (hardcoded) |
| `$timeout` | `60` (hardcoded) |
| Queue env | `EXTERNAL_CATALOG_ORDER_QUEUE`, else `EXTERNAL_CATALOG_IMPORT_QUEUE`, default `imports` |
| Horizon | `supervisor-import` watches `imports` (`config/horizon.php`) |
| Backoff | Not set on the class |
| Unique / middleware | None |

### Who dispatches

**Only** `App\Observers\OrderObserver::created`, registered in `App\Providers\EventServiceProvider` (`Order::observe(OrderObserver::class)`).

Inside `DB::afterCommit`:

```
if (app(ExternalCatalogOrderSettings::class)->isAuto()) {
    SubmitExternalOrderJob::dispatch($order->id);
}
```

`isAuto()` is invoked with **no** channel id (default catalog channel). Observer `updated` does **not** dispatch this job.

Admin manual send does **not** use this job.

No other `SubmitExternalOrderJob::dispatch` in this repository.

### `handle(OrderExportService $exporter, ExternalCatalogOrderSettings $settings): void`

1. If `! $settings->isAuto()` → return (mode may have flipped to manual while queued).
2. `Order::query()->find($this->orderId)` — missing → return.
3. If `$order->external_order_sync_status === OrderExportService::STATUS_SYNCED` (`synced`) → return.
4. `$result = $exporter->send($order)`.
5. If not `$result['success']`: `Log::warning('Auto external order export failed', order_id, error)`. Does **not** throw.

Because `OrderExportService::send()` catches `Throwable`, a failed Elkos POST **completes the job successfully**. `$tries = 3` does not re-queue those failures.

---

## `App\Jobs\ExternalCatalog\RebuildProductProjectionsJob`

**File:** `app/Jobs/ExternalCatalog/RebuildProductProjectionsJob.php`

**Not Elkos export.** Despite the namespace, `handle` only rebuilds denormalized `product_projections` via [catalog-products](../catalog-products/README.md) `ProductProjectionService::rebuildMany`.

Traits: `Batchable`, `Dispatchable`, `InteractsWithQueue`, `Queueable`, `SerializesModels`.

| Property | Value |
|---|---|
| Constructor | `public array $productIds` |
| `$tries` | `max(1, (int) config('catalog.job_tries', 3))` — env `CATALOG_REINDEX_JOB_TRIES` (`config/catalog.php` default 3) |
| `$timeout` | `max(30, (int) config('catalog.job_timeout', 300))` — config file default is **600** (`CATALOG_REINDEX_JOB_TIMEOUT`); the `300` applies only if the config key is missing |
| Queue | `onQueue((string) config('catalog.projection_queue', 'projections'))` |
| Queue env | `CATALOG_PROJECTION_QUEUE`, else `EXTERNAL_CATALOG_PROJECTION_QUEUE`, default `projections` |
| Horizon | `supervisor-projections` |

### `handle(ProductProjectionService $projections): void`

1. If `$this->batch()?->cancelled()` → return.
2. Try `DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED')`; swallow `Throwable` (fall back to connection default).
3. `runWithDeadlockRetry(fn () => $projections->rebuildMany($this->productIds))`.

### Deadlock retry (in-job, not Laravel attempts)

Up to **5** attempts. On `QueryException` if `isDeadlock` and attempt &lt; 5: `usleep(random_int(50_000, 100_000) * $attempt)` then retry. Class comment: `rebuildMany` is idempotent.

`isDeadlock`: SQLSTATE `40001`, or message contains `1213`, `1205`, `Deadlock`, or `Lock wait timeout`.

### Who dispatches

| Dispatcher | Batch name | Storefront ping on batch `then`? |
|---|---|---|
| `App\Console\Commands\RebuildProductProjections` (`products:reindex`, queued path) | `products:reindex` | **No** |
| `App\Services\Catalog\CatalogReindexDispatcher::dispatchProjectionRebuilds` | `catalog:projections` | **Yes** — `CatalogReindexStatus::projectionBatchFinished()` then `StorefrontRevalidator::ping()` |

Both wrap jobs in `Bus::batch`, `allowFailures()`, `onQueue($queue)`, chunks of **500 jobs** per batch. Product-id chunk size: `config('catalog.projection_chunk', 100)` (`CATALOG_PROJECTION_CHUNK` / `EXTERNAL_CATALOG_PROJECTION_CHUNK`). The artisan `--chunk` option is **not** used for the queued ExternalCatalog job size (`products:reindex` queued path uses `projection_chunk`).

Admin product save rebuilds **in-process** (`ProductProjectionService::rebuildMany`) and does not dispatch this job.
