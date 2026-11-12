# Dependencies

Verified from `use` statements and method calls in this module’s PHP.

---

## Inbound (this module uses)

### [settings-system](../settings-system/README.md)

| Piece | How |
|-------|-----|
| `App\Services\SettingService` | Consent store: `gdpr.consent_version`. Event store: `gdpr.analytics_tracking_enabled`, `gdpr.enabled`, `gdpr.analytics_require_consent`. Purge: `gdpr.analytics_retention_days`. |
| `config/settings.php` group `gdpr` | Schema, defaults, `public` flags. Admin **Settings** (`Admin\SettingController`) persists the same dotted keys per `users.current_channel_id`. |
| `GET /api/v1/settings` | `Api\V1\SettingController` + `SettingResource` — storefront reads `data.gdpr`. Not implemented in this module’s controllers. |

`SettingService::get` uses channel cache `app_settings_channel_{id}`, then config `default` if the key is missing.

**Keys in schema but not read by this module’s controllers/command:** `cookie_banner_*`, legal URL fields, `show_reject_button`, `consent_expiry_days`, `newsletter_consent_text`, `marketing_default_checked`, `analytics_anonymize_ip`. IP hashing always uses SHA-256 of ip + `config('app.key')` regardless of `analytics_anonymize_ip`.

### [auth](../auth/README.md)

| Piece | How |
|-------|-----|
| Sanctum `$request->user('sanctum')` | Optional on consent/events; required on privacy (`auth:sanctum`). |
| `verified.if-required` | Privacy routes only. |
| `App\Models\User` | `belongsTo` on both models; privacy `user_id`; admin DataTables `user` relation (`name`, `email`). |
| Admin `auth` + `role:Admin` | Storefront events UI. |

Auth README already notes GDPR privacy routes consume `auth:sanctum` and are out of that module’s ownership.

### [channels-locales](../channels-locales/README.md)

| Piece | How |
|-------|-----|
| `channel.api` / `app('currentChannel')` | All storefront consent/event actions. |
| `App\Models\Channel` | `belongsTo`; purge `Channel::query()->select(['id','code'])`; admin `where('channel_id', current_channel_id)`. |
| `locale.api` | On the `v1` group; these controllers do not branch on locale. |

### [users-access-control](../users-access-control/README.md)

`check.permission:storefront_event_view|storefront_event_delete`, Spatie permissions from `ModulePermissionSeeder` (`Storefront Events`). Super Admin / access-level resolution is that module’s `AuthServiceProvider` / `AutoAuthorizePermission` path.

### Framework / packages

| Dependency | Role |
|------------|------|
| Laravel FormRequest / Validator | Consent and event bodies |
| `hash('sha256', …)` + `config('app.key')` | `ip_hash` (do not log or commit the key) |
| `throttle:120,1` | Event POST |
| Yajra DataTables | Admin index AJAX |
| `Illuminate\Console\Command` | Purge |

---

## Outbound (who reads this module’s tables)

| Caller | Behavior |
|--------|----------|
| `App\Services\Cms\TrendingProductWidgetResolver` | Ranks `entity_type = product` events with `consent = true`, `event_type` click and/or `add_to_cart`, `occurred_at` within widget `window_days`, numeric `entity_id`. Cache TTL 900s. Owned by [cms-pages-widgets](../cms-pages-widgets/README.md). |
| Admin Blade | `resources/views/admin/storefront_events/{index,show,actions}.blade.php` |
| Widget admin hint | `resources/views/admin/widgets/partials/trending_products.blade.php` comment: ranked from `storefront_events` |

No Laravel events, jobs, notifications, or mail classes are dispatched from the GDPR controllers or purge command.

---

## Not a dependency of this module

- Catalog/cart/order write paths do not call these controllers.
- Newsletter `consent` copy (`gdpr.newsletter_consent_text`) is not used in `StorefrontConsentController`.
- Storefront TypeScript (`docs-storefront/api/gdpr-tracking.md`) is a separate client tree; backend source of truth is this app.
