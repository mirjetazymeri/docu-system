# Models

Neither model uses `SoftDeletes`, factories, observers, `$hidden`, `$appends`, or a custom `$table`. Default table names: `storefront_consents`, `storefront_events`.

Inverse `hasMany` relations are **not** declared on `App\Models\User` or `App\Models\Channel`.

---

## `App\Models\StorefrontConsent`

**File:** `app/Models/StorefrontConsent.php`

### Fillable

`channel_id`, `user_id`, `visitor_id`, `session_id`, `consent_analytics`, `consent_marketing`, `consent_source`, `policy_version`, `preferences`, `ip_hash`, `user_agent`, `consented_at`, `withdrawn_at`

### Casts

| Attribute | Cast |
|-----------|------|
| `consent_analytics` | `boolean` |
| `consent_marketing` | `boolean` |
| `preferences` | `array` |
| `consented_at` | `datetime` |
| `withdrawn_at` | `datetime` |

### Relations

| Method | Type | Related |
|--------|------|---------|
| `channel()` | `belongsTo` | `App\Models\Channel` |
| `user()` | `belongsTo` | `App\Models\User` |

### Writers / readers in this module

| Caller | Usage |
|--------|--------|
| `StorefrontConsentController::store` | `firstOrNew($identity)` then `fill` + `save` |
| `StorefrontConsentController::show` / `revoke` | `where($identity)->latest('id')->first()` |
| `StorefrontEventController::resolveStoredAnalyticsConsent` | latest row; returns `(bool) consent_analytics` |
| `PrivacyController` | `where('user_id', $user->id)` get / delete |
| `PurgeStorefrontTelemetryCommand` | `withdrawn_at` not null and older than cutoff |

Event ingest does **not** read `consent_marketing`.

---

## `App\Models\StorefrontEvent`

**File:** `app/Models/StorefrontEvent.php`

### Fillable

`channel_id`, `user_id`, `visitor_id`, `session_id`, `event_type`, `event_name`, `entity_type`, `entity_id`, `url`, `referrer`, `search_term`, `payload`, `consent`, `ip_hash`, `user_agent`, `occurred_at`

### Casts

| Attribute | Cast |
|-----------|------|
| `payload` | `array` |
| `consent` | `boolean` |
| `occurred_at` | `datetime` |

### Relations

| Method | Type | Related |
|--------|------|---------|
| `channel()` | `belongsTo` | `App\Models\Channel` |
| `user()` | `belongsTo` | `App\Models\User` |

Admin list/show eager-loads `user` and `channel`.

### Writers / readers

| Caller | Usage |
|--------|--------|
| `Api\V1\StorefrontEventController::store` | `StorefrontEvent::create([...])` |
| `Admin\StorefrontEventController` | channel-scoped query, DataTables, `delete()` |
| `PrivacyController` | `where('user_id', …)` limited get / delete |
| `PurgeStorefrontTelemetryCommand` | `occurred_at` older than cutoff |
| `App\Services\Cms\TrendingProductWidgetResolver` | aggregates consented `product` click / add_to_cart (not owned by this module) |

Allowed `event_type` values are enforced in `StoreStorefrontEventRequest` and duplicated as `Admin\StorefrontEventController::EVENT_TYPES`: `click`, `search`, `view`, `navigation`, `add_to_cart`, `custom`.
