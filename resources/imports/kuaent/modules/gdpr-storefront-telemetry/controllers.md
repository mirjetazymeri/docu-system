# Controllers

Validation: [requests-validation.md](requests-validation.md). JSON shapes: [api.md](api.md). Routes: [routes.md](routes.md).

All API controllers in this module use `$request->user('sanctum')` for optional or required Sanctum identity (not the default `web` guard).

IP hashing (consent store + event store): if `$request->ip()` is a non-empty string, `hash('sha256', $ip . '|' . (string) config('app.key'))`; otherwise `ip_hash` is `null`. Raw IP is never written. Setting `gdpr.analytics_anonymize_ip` is **not** read.

---

## `App\Http\Controllers\Api\V1\StorefrontConsentController`

**File:** `app/Http/Controllers/Api/V1/StorefrontConsentController.php`

Public `v1` group (`channel.api`, `locale.api`). No `auth:sanctum` middleware. No named routes.

If `app('currentChannel')` is missing: HTTP **400** `{ success: false, message: "Channel not resolved" }` on `show`, `store`, and `revoke`.

### `show(Request $request)`

Resolves one row via `resolveConsent`. Always HTTP **200** when channel exists.

`data`: `consent_analytics`, `consent_marketing` (bool; missing row → `false`), `policy_version`, `consented_at` / `withdrawn_at` as ISO-8601 or `null`.

`meta.channel_id`, `meta.has_record` (whether a row was found).

### `store(StoreStorefrontConsentRequest $request, SettingService $settings)`

HTTP **201**.

1. `policy_version` = validated value, or `SettingService::get('gdpr.consent_version', 'v1', $channelId)` when empty.
2. `firstOrNew(identityQuery)` then `fill` + `save`.
3. `consent_source` empty → stored `'cookie_banner'`.
4. `consented_at` = `now()`.
5. `withdrawn_at` = `null` if `consent_analytics` is true; otherwise `now()`.
6. `user_id` = Sanctum id when present; `visitor_id` / `session_id` from body (nullable empty → `null`).

`consent_marketing` defaults to `false` when omitted (`$data['consent_marketing'] ?? false`).

### `revoke(Request $request)`

No FormRequest.

- No matching row → HTTP **404** `{ success: false, message: "Consent record not found." }`
- Else `forceFill`: `consent_analytics` false, `consent_marketing` false, `withdrawn_at` now. Does **not** delete the row.

HTTP **200** `{ success, message: "Consent revoked.", data: { id, withdrawn_at } }`.

### `resolveConsent` / `identityQuery` (protected)

`resolveConsent`: `where(identityQuery)->latest('id')->first()`.

`identityQuery` (same file):

| Condition | Query array |
|-----------|-------------|
| Sanctum `user_id` present | `channel_id`, `user_id` |
| Else `visitor_id` non-empty (input, else query) | `channel_id`, `visitor_id` |
| Else `session_id` non-empty | `channel_id`, `session_id` |
| Else | `channel_id`, `id` => `0` |

`store` uses the same identity for `firstOrNew` (first match, not `latest('id')`). If duplicate rows exist, show/revoke can target a different row than `firstOrNew`.

---

## `App\Http\Controllers\Api\V1\StorefrontEventController`

**File:** `app/Http/Controllers/Api/V1/StorefrontEventController.php`

Public `v1` group plus `throttle:120,1` on the route (120 requests / 1 minute, Laravel default limiter).

`StoreStorefrontEventRequest` runs **before** tracking/consent gates: invalid bodies are **422** even if tracking is off.

Missing channel: **400** same message as consent.

Settings (channel id):

| Variable | `SettingService::get` |
|----------|------------------------|
| `$trackingEnabled` | `gdpr.analytics_tracking_enabled`, default `false` |
| `$gdprEnabled` | `gdpr.enabled`, default `true` |
| `$requireConsent` | `gdpr.analytics_require_consent`, default `true` |

Consent boolean:

- If the request **has** key `consent` → `(bool) $request->boolean('consent')`
- Else `resolveStoredAnalyticsConsent()` (stored `consent_analytics` for identity; `false` if no identity or no row)

Gates (in order):

1. `!$trackingEnabled` → **202** `{ success: true, stored: false, reason: "tracking_disabled" }`
2. `$gdprEnabled && $requireConsent && !$consent` → **202** `{ success: true, stored: false, reason: "consent_required" }`
3. Else `StorefrontEvent::create(...)` → **201** `{ success: true, stored: true, data: { id, event_type, occurred_at }, meta: { channel_id } }`

Created fields: channel, Sanctum `user_id`, visitor/session from validated data, event fields, `consent` (the resolved boolean), `ip_hash`, `user_agent`, `occurred_at` (`validated` or `now()`).

### `resolveStoredAnalyticsConsent` (protected)

Same identity priority as consent (user → visitor → session). No identity → `false`. Query: `channel_id` + that key, `latest('id')`, `(bool) consent_analytics`.

---

## `App\Http\Controllers\Api\V1\PrivacyController`

**File:** `app/Http/Controllers/Api/V1/PrivacyController.php`

Inside `auth:sanctum` and `verified.if-required`. `$user = $request->user('sanctum')`. No FormRequests. **Not** filtered by `channel_id`.

Unverified users (when verification is required): middleware JSON **403** `{ message: "Email not verified.", code: "email_not_verified" }` — [auth](../auth/README.md).

### `export(Request $request)`

- Events: `where('user_id', $user->id)->orderByDesc('occurred_at')->limit(5000)->get()`
- Consents: `where('user_id', $user->id)->orderByDesc('consented_at')->get()` (no limit)

HTTP **200** `{ success, data: { events, consents }, meta: { events_count, consents_count } }`.

Serializes Eloquent models (all attributes; no `$hidden`). Not a file download: no `Content-Disposition`. Guest `visitor_id` rows with `user_id` null are **not** included.

### `destroyTracking(Request $request)`

Hard-deletes events and consents with that `user_id`. Returns counts from query `delete()`.

HTTP **200** `{ success, message: "Tracking data deleted for authenticated user.", data: { events_deleted, consents_deleted } }`.

Does not delete the `users` row or other modules’ data.

---

## `App\Http\Controllers\Admin\StorefrontEventController`

**File:** `app/Http/Controllers/Admin/StorefrontEventController.php`

Constructor:

| Middleware | Actions |
|------------|---------|
| `check.permission:storefront_event_view` | `index`, `show` |
| `check.permission:storefront_event_delete` | `delete` |

All queries: `StorefrontEvent::query()->where('channel_id', (int) auth()->user()->current_channel_id)`.

`EVENT_TYPES` constant: `click`, `search`, `view`, `navigation`, `add_to_cart`, `custom`.

### `index(Request $request)`

Non-AJAX: view `admin.storefront_events.index` with filter values + `eventTypes`.

AJAX (`$request->ajax()`): Yajra DataTables on `with(['user', 'channel'])`.

Query filters (GET, also sent by DataTables ajax `data`):

| Input | Behavior |
|-------|----------|
| `q` | LIKE on `event_type`, `event_name`, `search_term`, `url`, `entity_type`, `entity_id`, `visitor_id`, `session_id`, `CAST(payload AS CHAR)`; or user `name`/`email`; if `q` is all digits, also `id` or `user_id` |
| `event_type` | exact match if in `EVENT_TYPES` |
| `event_name` | LIKE |
| `entity_type` | LIKE |
| `session_id` | LIKE |
| `consent` | `'1'` or `'0'` → boolean `consent` |
| `from` / `to` | `whereDate(occurred_at, >= / <=)` |

Extra columns: `occurred`, `type_name`, `entity`, `actor`, `session`, `search_or_url`, `actions` (partial `admin.storefront_events.actions`). `consent` edited to badges. `rawColumns` includes those HTML columns.

Default DataTables order in the Blade script: column `occurred` desc.

### `show(int $id)`

`eventsQuery()->with(['user', 'channel'])->findOrFail($id)`. View `admin.storefront_events.show`. Displays event fields including `ip_hash` and `user_agent`. `{id}` is an integer, not route-model binding (wrong channel → 404).

### `delete(int $id)`

`findOrFail` + `delete()`. Redirect `admin.storefront_events.index` with flash `translations.admin.storefront_events.messages.deleted`.

There is **no** admin UI for `StorefrontConsent`.
