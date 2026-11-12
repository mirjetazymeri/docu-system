# Settings & system models

Eloquent models owned by this module: `App\Models\Setting` and `App\Models\AdminThemeSetting`.

There is no Eloquent model for cache/optimize/revalidate; that path is `SystemCacheService` plus Laravel’s cache store.

## Setting

**File:** `app/Models/Setting.php`

| | |
|--|--|
| Table | `settings` |
| Fillable | `key`, `value`, `type`, `channel_id` |
| Casts | none |
| Hidden / appends | none |

`SettingService` writes only `key`, `channel_id`, and `value` via `updateOrCreate`. Fillable `type` is **never set** by that service. Column comments in the create migration (`text`, `file`, `json`, `color`) are not enforced.

`value` is a nullable text column. Arrays passed to `SettingService::setMany()` are `json_encode`d before save. `SettingService::get()` JSON-decodes string values when `json_decode` reports `JSON_ERROR_NONE`.

### Relations

| Method | Type | Notes |
|--------|------|--------|
| `channel()` | `belongsTo(Channel::class)` | FK `channel_id`; `cascadeOnDelete` on the column |

No `HasFactory`. No scopes. Channel uniqueness is a DB unique index on `(key, channel_id)`, not a model rule.

Admin and API never query `Setting` directly except inside `SettingService`.

## AdminThemeSetting

**File:** `app/Models/AdminThemeSetting.php`

| | |
|--|--|
| Table | `admin_theme_settings` |
| Fillable | `options` |
| Casts | `options` → `array` |

No `channel_id`. The panel is one global blob.

`AdminThemeService` loads `AdminThemeSetting::query()->latest('id')->value('options')` (or the latest row for update). Multiple rows are possible; **latest `id` wins**. `reset()` deletes **all** rows.

Allowed option keys are `array_keys(config('admin_theme.defaults'))` — see [services.md](services.md). Unknown keys in a POST are dropped on save.

### Relations

None.

## Related models (not owned)

| Model | How this module uses it |
|-------|-------------------------|
| `Channel` | Settings are keyed by `channel_id`. `SettingService::resolveChannelId()` falls back to `Channel::query()->orderBy('id')->value('id')` then `0`. `SystemCacheService::clearStorefrontDataCaches()` iterates all channel ids. |
| `TaxClass` | `SettingController::hydrateDynamicSchema()` plucks `code` for `tax.shipping.tax_class_code` select options. |
| `User` | Admin save uses `auth()->user()->current_channel_id` and `channels()` pivot membership (403 if the current channel is not assigned). |

`App\Support\AuthSettings` is **not** a model. It reads `setting()` keys; owned by [auth](../auth/README.md).
