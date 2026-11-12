# Commands — External catalog

Laravel 11 auto-discovers `app/Console/Commands`. `routes/console.php` does not register this command.

---

## `storefront:verify-revalidate`

**Class:** `App\Console\Commands\VerifyStorefrontRevalidate`  
**File:** `app/Console/Commands/VerifyStorefrontRevalidate.php`

| | |
|---|---|
| Signature | `storefront:verify-revalidate {--ping : POST to the storefront /api/revalidate endpoint}` |
| Description | `Verify storefront ISR revalidation env (STOREFRONT_REVALIDATE_*)` |

The class **imports** `StorefrontRevalidator` but does **not** call it. The `--ping` path duplicates `StorefrontRevalidator::attempt()` HTTP (timeout 15, header `x-revalidate-secret`, JSON `{tags}`).

### Without `--ping`

Prints (secret **value** is never printed):

| Line | Content |
|---|---|
| `STOREFRONT_REVALIDATE_URL` | URL, or `(not set — ping disabled)` |
| `STOREFRONT_REVALIDATE_SECRET` | `set (N chars)` or `(not set)` |
| `STOREFRONT_REVALIDATE_TAGS` | `implode(', ', $tags)` from config |

If URL or secret is empty: warning that revalidation is disabled; notes ISR will refresh on storefront time windows only; exit `FAILURE` (`1`).

If both set: tells operator to run with `--ping`; exit `SUCCESS` (`0`).

### With `--ping`

Requires URL and secret (same early `FAILURE` as above).

`POST` config URL with tags from `config('external_catalog.revalidate_tags')`.

| HTTP | Command |
|---|---|
| Successful | `Storefront revalidation OK.` → `SUCCESS` |
| 503 | Error: storefront `REVALIDATE_SECRET` likely not loaded; restart Next.js → `FAILURE` |
| 401 or 403 | Error: secret mismatch (`STOREFRONT_REVALIDATE_SECRET` vs storefront `REVALIDATE_SECRET`) → `FAILURE` |
| Other status | `Unexpected response from storefront.` → `FAILURE` |
| `Throwable` | Prints exception message; example `curl` using the URL and a **placeholder** secret (`…`) → `FAILURE` |

Prints HTTP status and JSON body when the body is an array.

---

## Not this module

`php artisan products:reindex` (`App\Console\Commands\RebuildProductProjections`) dispatches `RebuildProductProjectionsJob` but is owned by [catalog-products](../catalog-products/README.md).
