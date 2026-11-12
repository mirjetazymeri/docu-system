# Controllers

Base class: `App\Http\Controllers\Controller` (`AuthorizesRequests`, `DispatchesJobs`, `ValidatesRequests`).

There are no `app/Http/Requests` classes for channels, locales, or translations. Validation is inline; see [requests-validation.md](requests-validation.md).

DataTables: `Yajra\DataTables\Facades\DataTables`.

## `App\Http\Controllers\Admin\ChannelController`

Path: `app/Http/Controllers/Admin/ChannelController.php`

Constructor injects `App\Services\ChannelLocaleService`.

Middleware (`check.permission` → `App\Http\Middleware\AutoAuthorizePermission`):

| Permission | Methods |
| --- | --- |
| `channel_view` | `index`, `switch` |
| `channel_create` | `create` |
| `channel_edit` | `edit` |
| `channel_delete` | `delete` |

### `index(Request $request)`

- AJAX: `Channel::query()->with('locales')` → DataTables. Columns include `domain` (empty shown as `—`), HTML locale badges (`pivot.is_default` → `bg-primary`, else `bg-secondary`), empty locales → `__('translations.admin.channels.all_active')`, actions view `admin.channels.actions`.
- Non-AJAX: `view('admin.channels.index')`.

### `create(Request $request)`

GET and POST on the same action (`Route::match`).

POST: validates, `Channel::create($request->only('name', 'code', 'domain'))`, then:

- empty `locale_ids` → `attachAllActiveLocales($channel)`
- else `syncChannelLocales($channel, $localeIds, $default_locale_id)`

Redirect `admin.channels.index` with flash `Channel created.`

GET: `view('admin.channels.create', $this->formExtras())`.

### `edit(Request $request, $id)`

`Channel::with('locales')->findOrFail($id)`. POST updates `name`, `code`, `domain` and the same locale sync rules. Flash `Channel updated.` GET: `admin.channels.edit`.

### `delete($id)`

`findOrFail`. If `code === 'default'`, `back()` with error `Cannot delete default channel.` Else delete, `flushApiCache($channelId)`, redirect with `Channel deleted.`

### `switch($id)`

If the authenticated user has no `channels` row for that id: `abort(403, 'Unauthorized channel')`. Else `current_channel_id` = `$id`, refresh, `back()` with `Channel switched.`

Route parameter name is `{channel}` but the method argument is `$id` (value is the channel id from the URL).

### `formExtras(?Channel $channel = null)`

Active locales (`is_active`, order `is_default` desc, `code` asc), selected ids from `old('locale_ids')` or the channel, default from `old('default_locale_id')` or pivot `is_default`.

### Views

| View | Path |
| --- | --- |
| index | `resources/views/admin/channels/index.blade.php` |
| create | `resources/views/admin/channels/create.blade.php` |
| edit | `resources/views/admin/channels/edit.blade.php` |
| form | `resources/views/admin/channels/_form.blade.php` |
| actions | `resources/views/admin/channels/actions.blade.php` |

Create form instantiates `new \App\Models\Channel()`. Flag images in the header use `asset('build/assets/images/flags/' . $flagFile)`.

## `App\Http\Controllers\Admin\LocaleController`

Path: `app/Http/Controllers/Admin/LocaleController.php`

Injects `ChannelLocaleService`.

| Permission | Methods |
| --- | --- |
| `locale_view` | `index`, `switch` |
| `locale_create` | `create`, `store` |
| `locale_edit` | `edit`, `update` |
| `locale_delete` | `destroy` |

`Route::resource('locales', LocaleController::class)` also registers `show` (`admin.locales.show`). This class has **no** `show` method.

### `index`

AJAX DataTables on `Locale::query()->orderByDesc('is_default')->orderBy('code')`. `is_active` / `is_default` rendered via `__('translations.admin.common.yes')` / `no`. Actions: `admin.locales.actions`.

Non-AJAX: `admin.locales.index`.

### `create` / `store`

`admin.locales.create`. Store normalizes `code` with `strtolower(str_replace('_', '-', ...))`, booleans from `$request->boolean('is_active'|'is_default')`, `Locale::create`, `flushLocaleCaches()`, flash `Locale created successfully.`

### `edit` / `update`

Route-model binding `Locale $locale`. Update same field rules + unique ignore current id. Flash `Locale updated successfully.`

### `destroy(Locale $locale)`

If `is_default`: `back()` error `Cannot delete default locale.` Else delete, flush caches, flash `Locale deleted.`

### `switch($locale)`

Normalizes the code. Allowed list: `channel_locale_codes()`. If not in that list: `abort(400, 'Invalid language.')`. Always `session()->put('locale', $locale)`. If authenticated, `auth()->user()->update(['locale' => $locale])`. `return back()` (no flash).

### `flushLocaleCaches()` (private)

`Cache::forget('locales.active')`, `Cache::forget('app:active_locales')`, `$this->channelLocales->flushApiCache()`.

### Views

`resources/views/admin/locales/{index,create,edit,_form,actions}.blade.php`.

## `App\Http\Controllers\Admin\TranslationController`

Path: `app/Http/Controllers/Admin/TranslationController.php`

No `ChannelLocaleService`. Locale columns come from `array_keys(config('locales'))`.

| Permission | Methods |
| --- | --- |
| `translation_view` | `index` |
| `translation_create` | `store` |
| `translation_edit` | `update` |
| `translation_delete` | `destroy` |

### `index`

AJAX: load each `lang/{locale}/translations.php`, flatten with `Illuminate\Support\Arr::dot`, keep scalar/null leaves, union of keys, one row per key, per-locale `<input name="translations[{locale}]">` (readonly unless `auth()->user()?->can('translation_edit')`). Actions: `admin.translations.actions`.

Non-AJAX: `admin.translations.index` with `$locales`.

### `update`

Reads `$request->key` and `$request->translations` (expected map locale → text). For each pair, include file (or `[]`), `setTranslationValue` (dotted keys via `data_set`), `writeTranslationFile`. `back()` with `Translation updated.`

### `store`

Validates `key` required string. Same write loop using `$request->translations`. Flash `Translation added.`

### `destroy($key)`

For each config locale, if the file exists, `forgetTranslationValue` (dotted via `Arr::forget`) and rewrite. Flash `Translation deleted.`

### File writer

`file_put_contents($path, "<?php\n\nreturn " . var_export($langArray, true) . ";\n")`.

### Views

`resources/views/admin/translations/index.blade.php`, `resources/views/admin/translations/actions.blade.php`. Add form posts to `admin.translations.store` when `@can('translation_create')`.

## `App\Http\Controllers\Api\V1\LocaleController`

Path: `app/Http/Controllers/Api/V1/LocaleController.php`

Injects `ChannelLocaleService`. Method `index()` only. See [api.md](api.md).

## Admin navigation

`resources/views/layouts/components/main-sidebar.blade.php`:

- Localization (needs `translation_view` or `locale_view`): `url('admin/translations')`, `url('admin/locales')`
- System configs: `route('admin.channels.index')` behind `@can('channel_view')`

Header switchers: `resources/views/layouts/components/main-header1.blade.php` (master layout). Alternate `resources/views/layouts/components/main-header.blade.php` also links `admin.locale.switch`.
