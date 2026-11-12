# Settings & system controllers

Admin controllers live under `App\Http\Controllers\Admin`. The public API controller is `App\Http\Controllers\Api\V1\SettingController`.

There are **no** `App\Http\Requests\*` classes for this module. Validation: [requests-validation.md](requests-validation.md). Routes: [routes.md](routes.md).

`App\Http\Controllers\Admin\LookupController` is **not** part of this module (catalog/shipping/promotion pickers).

## Shared admin behavior

- Constructor `check.permission:*` middleware (`AutoAuthorizePermission`). Super Admin skips the check; others need the named permission via access level.
- Admin group also requires `auth`, `verified.if-required`, `set.user.locale`, `role:Admin`.
- Settings **update** / **testEmail** additionally `abort_unless` the user’s `channels()` pivot contains `current_channel_id` (HTTP 403). Theme and system-cache actions do not repeat that channel check (theme is global; cache is app-wide).

## `SettingController` (admin)

**File:** `app/Http/Controllers/Admin/SettingController.php`

| Actions | Permission |
|---------|------------|
| `index` | `setting_view` |
| `update`, `testEmail` | `setting_edit` |

Views: `resources/views/admin/settings/index.blade.php`, `_form.blade.php`, `_footer_column_content.blade.php`, `_footer_contact_line_row.blade.php`.

### `index(SettingService $settings)`

Returns `admin.settings.index` with:

- `settings` → `$settings->all()` (resolved channel, no argument)
- `schema` → `hydrateDynamicSchema(config('settings'))`

### `update(Request $request, SettingService $settings)`

1. `$channelId = auth()->user()->current_channel_id`; 403 unless assigned to that channel.
2. Flatten hydrated schema; build rules via `buildValidation()`; extra footer array rules; `$request->validate($rules)`.
3. `FooterSections::validateColumnContentInput($request->input('footer.column_content', []))`.
4. Start from `$settings->all($channelId)`.
5. For each schema field:
   - **file**: if `$request->hasFile($key)` (dot key, e.g. `general.logo`), delete old path on `public` disk if it exists, `store('settings', 'public')`. If no new file, keep existing.
   - **boolean**: `array_key_exists` on `Arr::dot($request->except('_token', '_method'))`; `filter_var(..., FILTER_VALIDATE_BOOLEAN)` or `false` if absent (hidden `0` + checkbox `1` in the Blade).
   - **footer_column_content**: `FooterSections::sanitizeColumnContentInput(...)`.
   - else: update only if the dotted key exists in the request; value from `$validated[$key] ?? $flatInput[$key]`.
6. `$settings->setMany($data, $channelId)`.
7. Redirect **back**, flash `Settings updated`.

The form is `PUT` `admin.settings.update` with `enctype="multipart/form-data"`.

### `testEmail(Request $request)`

Same channel 403 as `update`. Sends to `auth()->user()->email`. Body JSON/form field `template` (default `'welcome'`).

| `template` | Mailable |
|------------|----------|
| `welcome` | `App\Mail\WelcomeMail` |
| `verify-email` | `App\Mail\VerifyEmailMail` with `route('verification.verify', [$user->id, 'hash' => 'TEST-HASH'])` |
| `login-alert` | `App\Mail\LoginAlertMail` (IP, user agent, `now()` UTC string) |
| anything else | HTTP 400 `Unknown template` |

Does **not** consult `notifications.user.*` toggles. JSON `{ success, message }`; catch-all 500 with exception message on failure.

The settings index Blade POSTs JSON `{ template }` to `admin.settings.test-email` with CSRF.

### Helpers (protected)

| Method | Role |
|--------|------|
| `hydrateDynamicSchema(array $schema)` | If any `TaxClass` has a non-empty `code`, sets `tax.shipping.tax_class_code` `type` to `select` and `options` to those codes (`orderBy('name')`). |
| `keyToInputName(string $key)` | `general.logo` → `general[logo]` (nested brackets). |
| `flattenSchema(array $schema, string $prefix = '')` | Leaves are nodes with a `type` key. |
| `buildValidation(array $field)` | See [requests-validation.md](requests-validation.md). |

## `AdminThemeController`

**File:** `app/Http/Controllers/Admin/AdminThemeController.php`

Constructor-injected `AdminThemeService $theme`.

| Actions | Permission |
|---------|------------|
| `edit` | `theme_view` |
| `update`, `reset` | `theme_edit` |

Views: `resources/views/admin/theme/edit.blade.php` plus `_colors`, `_sidebar`, `_header`, `_layout`, `_options`.

### `edit()`

`admin.theme.edit` with `theme` → `$this->theme->all()`, `options` → `config('admin_theme.options')`, `primaryPresets` / `backgroundPresets` from config (empty array if missing).

### `update(Request $request)`

Validates colors plus every key of `config('admin_theme.options')` with `Rule::in(array_keys(config('admin_theme.options.'.$key)))`.

Then:

- If `use_custom_background` is not boolean-true, forces `background_color` to `''`.
- If `nav_layout === 'horizontal'` and `nav_style` empty → `nav_style = 'menu-click'`.
- If `nav_layout === 'vertical'` and `nav_style` is non-empty → `vertical_style` stays or becomes `'overlay'`.

Saves `collect($validated)->except('use_custom_background')`. Redirect `admin.theme.edit`, flash `Admin theme saved.`

`use_custom_background` is UI-only (not in `admin_theme.defaults`).

### `reset()`

`$this->theme->reset()`. Redirect `admin.theme.edit`, flash `Admin theme reset to defaults.`

## `SystemCacheController`

**File:** `app/Http/Controllers/Admin/SystemCacheController.php`

| Actions | Permission |
|---------|------------|
| `index` | `system_cache_view` |
| `clear`, `optimize`, `revalidate`, `refreshAll` | `system_cache_edit` |

View: `resources/views/admin/system_cache/index.blade.php`.

### `index(SystemCacheService $cache)`

Passes `status` from `$cache->status()`.

### Mutations

Each calls the matching service method and `redirectWithResult()`:

| Method | Service |
|--------|---------|
| `clear` | `clearApplicationCaches()` |
| `optimize` | `optimizeApplication()` |
| `revalidate` | `revalidateStorefront()` — maps `ok` to `ok \|\| skipped`; `warning` if skipped or not ok |
| `refreshAll` | `refreshAll()` — `warning` if revalidate neither `ok` nor `skipped` |

### `redirectWithResult(array $result)`

Flash key:

- `error` if `ok` is false
- else `warning` if `warning` is true
- else `success`

Redirect `admin.system_cache.index` with that flash message and `cache_steps` = `steps` (list of strings).

## `SettingController` (API)

**File:** `app/Http/Controllers/Api/V1/SettingController.php`

No permission middleware. Group: prefix `v1`, middleware `channel.api`, `locale.api` (Laravel `/api` prefix → **`GET /api/v1/settings`**).

### `index(SettingService $settings)`

- Resolves `app('currentChannel')`. Missing channel → JSON 400 `{ success: false, message: 'Channel not resolved' }`.
- `Cache::rememberForever("api.settings.v1.channel_{$channelId}", fn () => new SettingResource($settings->all($channelId)))`.
- JSON 200 `{ success: true, data: <resource>, meta: { channel_id } }`.

The remembered value is the `SettingResource` object, not a raw array.

## `SettingResource`

**File:** `app/Http/Resources/Api/V1/SettingResource.php`

Walks `config('settings')` the same way as schema flatten:

- Field node (`type` set): skip if `public` is empty; value = stored `$this[$fullKey]` else schema **`default`** (not `value`).
- `file` + non-empty: `asset('storage/' . $val)`.
- `footer_column_content`: `FooterSections::normalizeColumnContentForApi($val, FooterSections::parse($sectionsRaw))` where `$sectionsRaw` is stored `footer.sections` or schema default.
- Nested groups included only if the child tree is non-empty.

Private groups (`notifications` except the two public user flags, most `tax.*`, search FULLTEXT flags, etc.) are omitted from `data`.
