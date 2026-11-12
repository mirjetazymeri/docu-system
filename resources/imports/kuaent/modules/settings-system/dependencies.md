# Settings & system dependencies

This module **owns** persistence and admin/API surfaces for settings, admin theme, and cache/revalidate buttons. Other modules **read** keys via `setting()` / `SettingService::get()`.

## Inbound (this module uses)

| Dependency | How |
|------------|-----|
| `App\Models\Channel` | Settings `channel_id`; fallback first channel; iterate ids on cache clear |
| `App\Models\TaxClass` | `SettingController::hydrateDynamicSchema()` option list |
| `App\Models\User` `current_channel_id` + `channels()` | Admin settings save/test-email 403 gate |
| `SetCurrentChannel` / `ResolveApiChannel` | `app('currentChannel')` for `SettingService::resolveChannelId()` |
| `App\Services\ChannelLocaleService::flushApiCache()` | After system-cache clear |
| `App\Services\ExternalCatalog\StorefrontRevalidator::attempt()` | Admin revalidate / refresh-all |
| `Illuminate\Support\Facades\Artisan` | `cache:clear`, `optimize:clear`, `optimize` |
| `Illuminate\Support\Facades\Cache` | Forever caches + tagged flush (optional) |
| `Illuminate\Support\Facades\Storage` disk `public` | Setting file replace/delete |
| `Illuminate\Support\Facades\Mail` | Admin test email only |
| Spatie/access-level permissions | `check.permission:*` |
| `config/settings.php`, `config/admin_theme.php`, `config/cache.php`, `config/external_catalog.php` | Schema, theme, driver name, revalidate URL/secret/tags |

## Outbound — storefront ISR

`SystemCacheService::revalidateStorefront()` and `refreshAll()` call **`StorefrontRevalidator::attempt()`**. Implementation, HTTP contract (`POST` JSON `{ tags }`, header `x-revalidate-secret`), and coalesced `pingAfterCommit` after catalog edits belong to [external-catalog](../external-catalog/README.md).

This module does **not** call artisan `storefront:verify-revalidate` (`App\Console\Commands\VerifyStorefrontRevalidate`). That diagnostic is the same env surface; document it under external-catalog, not here.

Env names: [README.md](README.md#env-vars-names-only).

## Outbound — who reads settings

Callers are not APIs of this module; they own their workflows.

### Auth — [auth](../auth/README.md)

`AuthSettings`: `general.admin_register_enabled`, `notifications.user.email_verification`, `notifications.user.password_reset`.

### Tax — [tax](../tax/README.md)

`TaxSettings` / `tax_config()`: `tax.*` plus `store.country` fallback for origin.

### Catalog — [catalog-products](../catalog-products/README.md)

`CatalogFacetSettings`, `CatalogSearchSettings`. Cache keys `products:dataset_hash` and `catalog:pp_fulltext_index` are **forgotten** on system-cache clear (written by catalog/projection code).

### External catalog — [external-catalog](../external-catalog/README.md)

`ExternalCatalogOrderSettings` (`product.external_order.mode`). Revalidator class as above.

### Cart / checkout / payments — [cart-checkout](../cart-checkout/README.md), [payments](../payments/README.md)

`CheckoutAddressFields` (`checkout.guest_enabled`, `checkout.address_fields.*`, fallback `system.checkout`). Payment drivers `CashOnDeliveryDriver`, `PcbBankDriver`, `RaiAcceptDriver` read `system.checkout` for guest carts.

### Orders / invoices — [orders](../orders/README.md)

`InvoiceService` reads `store.*` identity fields, `general.logo`, `general.app_name`, `store.invoice_prefix`, `store.invoice_footer_note`.

### Mail toggles

`App\Services\Mail\MailService` reads `notifications.admin.*` (except `low_stock_threshold`), `notifications.user.welcome_email` / `login_alert`, `notifications.order.{mapped status}`, `notifications.cart.abandoned_email`. Mailables use `notifications.mail.from_address` / `from_name`.

SMTP `notifications.mail.driver|host|port|encryption|username|password` are **stored only**; no `app/` overlay onto Laravel mail config.

### GDPR telemetry — [gdpr-storefront-telemetry](../gdpr-storefront-telemetry/README.md)

`StorefrontEventController` (API): `gdpr.analytics_tracking_enabled`, `gdpr.enabled`, `gdpr.analytics_require_consent`. `StorefrontConsentController::store`: `gdpr.consent_version`. `PurgeStorefrontTelemetryCommand`: `gdpr.analytics_retention_days`.

### SEO API

`App\Http\Controllers\Api\V1\SeoController::show` uses `seo.meta_title`, `seo.meta_description`, `seo.og_image` as fallbacks for product/category/page SEO. Catalog/CMS own the entity payload; this module owns the fallback keys.

### Channels / locales — [channels-locales](../channels-locales/README.md)

System-cache clear forgets `locales.active`, `app:active_locales`, `api.locales.v1`, then `ChannelLocaleService::flushApiCache()`.

### CMS — [cms-pages-widgets](../cms-pages-widgets/README.md)

Footer **column content** JSON is settings. Page `footer_section` assignment is CMS.

### Admin chrome

`resources/views/layouts/master.blade.php`, `styles.blade.php`, `brand-logo.blade.php`: `admin_theme()`, `app_name()`, `setting_file_url('general.logo'|'general.favicon')`.

### Newsletter / contact — [newsletter-contact](../newsletter-contact/README.md)

Consumes `setting()` for GDPR newsletter copy / contact admin email via mail service; does not own the schema.

## Cache tags flushed on clear

`channel:{id}` and `categories` via `Cache::tags()->flush()` (no-op on drivers without tags). Writers of those tags are catalog/channel code, not this module.

## Package / PHP

No extra Composer package beyond Laravel. Admin lists here are Blade forms, not Yajra DataTables.

## Sibling README links

This module’s folder is **`docs/modules/settings-system/`**. Other docs should link here, not a non-existent `docs/modules/settings/`.
