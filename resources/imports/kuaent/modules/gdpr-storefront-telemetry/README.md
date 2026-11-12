# GDPR storefront telemetry

Storefront **consent records**, **analytics event ingest**, authenticated **privacy export/delete**, admin event inspection, and a **manual retention purge** command.

This folder documents **implemented PHP behavior** in `TestDocu-admin` only. It is not a legal opinion, DPIA, or claim of GDPR/ePrivacy compliance.

Canonical frontend notes (do not treat as backend source of truth when they diverge):

- Root [README.md](../../../README.md) — section **GDPR + Storefront Tracking Quick Guide**
- [FRONTEND_IMPLEMENTATION.md](../../../FRONTEND_IMPLEMENTATION.md) — section **GDPR + Storefront Tracking**

## What this module owns

| Area | Classes / files |
|------|-----------------|
| Consent API | `App\Http\Controllers\Api\V1\StorefrontConsentController` |
| Event ingest API | `App\Http\Controllers\Api\V1\StorefrontEventController` |
| Privacy API | `App\Http\Controllers\Api\V1\PrivacyController` |
| Admin events | `App\Http\Controllers\Admin\StorefrontEventController` |
| Models | `App\Models\StorefrontConsent`, `App\Models\StorefrontEvent` |
| FormRequests | `StoreStorefrontConsentRequest`, `StoreStorefrontEventRequest` |
| Purge | `App\Console\Commands\PurgeStorefrontTelemetryCommand` (`gdpr:purge-telemetry`) |
| Tables | `storefront_consents`, `storefront_events` |

## What this module does not own

- Cookie banner UI, `visitor_id` / `session_id` generation, or localStorage — storefront client ([FRONTEND_IMPLEMENTATION.md](../../../FRONTEND_IMPLEMENTATION.md))
- `GET /api/v1/settings` payload construction — [settings-system](../settings-system/README.md) (`SettingController` + `SettingResource`)
- Sanctum login/register — [auth](../auth/README.md)
- Channel resolution (`channel.api`) — [channels-locales](../channels-locales/README.md)
- CMS **trending products** ranking that *reads* `storefront_events` — [cms-pages-widgets](../cms-pages-widgets/README.md)
- Newsletter subscribe/unsubscribe, contact form, or account deletion
- Guest privacy export/delete (only authenticated `user_id` rows)

## Runtime surfaces

| Surface | Entry |
|---------|--------|
| Public API | `GET/POST/DELETE /api/v1/storefront/consent`, `POST /api/v1/storefront/events` |
| Authenticated API | `GET /api/v1/me/privacy/export`, `DELETE /api/v1/me/privacy/tracking` |
| Admin | `/admin/storefront-events` (Pages sidebar) |
| CLI | `php artisan gdpr:purge-telemetry` |

**Schedule:** `gdpr:purge-telemetry` is **not** registered in `routes/console.php`. That file only schedules `rentals:release-expired-holds` every ten minutes. Purge runs only when invoked.

## Settings this code actually reads

Via `App\Services\SettingService::get` (channel-scoped):

| Key | Used by | Config default (`config/settings.php`) |
|-----|---------|----------------------------------------|
| `gdpr.analytics_tracking_enabled` | Event ingest | `false` |
| `gdpr.enabled` | Event ingest (consent gate) | `true` |
| `gdpr.analytics_require_consent` | Event ingest | `true` |
| `gdpr.consent_version` | Consent `POST` when `policy_version` empty | `v1` |
| `gdpr.analytics_retention_days` | Purge command (when `--days` omitted) | `180` |

Other `gdpr.*` schema fields (banner copy, legal URLs, `consent_expiry_days`, `newsletter_consent_text`, `marketing_default_checked`, `analytics_anonymize_ip`) are **not** referenced under `app/Http/Controllers/Api/V1/Storefront*`, `PrivacyController`, or `PurgeStorefrontTelemetryCommand`. Banner/legal keys are public on `GET /api/v1/settings` → `data.gdpr` for the storefront. See [dependencies.md](dependencies.md).

## Identity (implemented)

Consent lookup and event consent lookup prefer, in order:

1. Sanctum user (`$request->user('sanctum')?->id`) → `channel_id` + `user_id`
2. Else non-empty `visitor_id`
3. Else non-empty `session_id`
4. Else consent show/revoke query uses `channel_id` + `id = 0` (no real row); event stored-consent lookup returns `false`

There is **no** backend merge of guest (`visitor_id`) rows onto a user at login.

## Documentation index

| File | Contents |
|------|----------|
| [models.md](models.md) | `StorefrontConsent`, `StorefrontEvent` |
| [database.md](database.md) | Tables, FKs, indexes, migrations |
| [controllers.md](controllers.md) | API + admin controllers |
| [commands.md](commands.md) | `gdpr:purge-telemetry` |
| [routes.md](routes.md) | API, admin, console |
| [api.md](api.md) | JSON contracts |
| [requests-validation.md](requests-validation.md) | FormRequests |
| [workflows.md](workflows.md) | Consent, ingest, export, delete, purge |
| [dependencies.md](dependencies.md) | Settings, auth, channel, CMS reader |
| [tests.md](tests.md) | `tests/` coverage (none) |
