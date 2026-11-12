# Architecture

## Components

```
Admin web (Blade)
  SetCurrentChannel  →  app('currentChannel') from User::currentChannel
  set.user.locale    →  app()->setLocale() from User.locale / session / config('app.locale')
  ChannelController / LocaleController / TranslationController

HTTP boot
  AppServiceProvider::boot() (not console)
    Cache locales.active → config('locales'), config('app.supported_locales'), config('app.locale')
    view composer * → availableLocales, userChannels, currentChannel, channelLocales

Storefront API (/api/v1/*)
  channel.api (ResolveApiChannel)  then  locale.api (SetLocaleFromRequest)
  GET locales → Api\V1\LocaleController

Sitemaps (web.php, not /api)
  locale.api then channel.api  (order differs from API)
```

## Web pipeline

`bootstrap/app.php` appends `App\Http\Middleware\SetCurrentChannel` to the `web` group.

`set.user.locale` is **not** on the global `web` group (that line is commented). It is applied on these groups in `routes/web.php`:

- `auth` + `verified.if-required` + `set.user.locale` (profile)
- same + `role:Admin` (admin prefix, including channels/locales/translations)
- same + `role:Customer`
- same + `role:Sales Specialist`

`App\Http\Middleware\RoleMiddleware` requires `hasAnyRole(...)`. Channel and locale **switch** routes live only in the Admin group (`role:Admin`). Header dropdowns in `resources/views/layouts/components/main-header1.blade.php` (included from `resources/views/layouts/master.blade.php`) call `route('admin.locale.switch')` and `route('admin.channels.switch', ...)`.

### `SetCurrentChannel` (`App\Http\Middleware\SetCurrentChannel`)

If the user is not authenticated, the middleware continues without binding.

If authenticated:

1. If `current_channel_id` is empty, set it to `$user->channels()->first()` when that relation has a row, then `$user->refresh()`.
2. If `current_channel_id` is set but `$user->channels()->where('channel_id', $user->current_channel_id)` does not exist, set `current_channel_id` to `$user->channels()->first()?->id` (may be `null`).
3. `app()->instance('currentChannel', $user->currentChannel)`.

### `SetUserLocale` (`App\Http\Middleware\SetUserLocale`)

Resolution:

1. Authenticated user with non-empty `locale` column
2. Else `session('locale')` if the session has `locale`
3. Else `config('app.locale')` (after HTTP hydration this may be the DB default locale)

Then `app()->setLocale($locale)` when the value is a non-empty string.

Admin locale switch (`LocaleController::switch`) writes `session('locale')` and, if authenticated, `users.locale`.

## API pipeline

`routes/api.php` wraps prefix `v1` with `middleware(['channel.api', 'locale.api'])`.

Laravel’s default API prefix is `api`, so the locales endpoint is `GET /api/v1/locales`.

### `ResolveApiChannel` (`App\Http\Middleware\ResolveApiChannel`)

Looks up `App\Models\Channel` in this order; first hit wins:

1. Query `?channel=` matched on `channels.code`
2. Header `X-Channel` matched on `channels.code`
3. If `auth()->check()`, `auth()->user()->currentChannel`
4. `Channel::where('code', 'default')->first()`

Then:

- `app()->instance('currentChannel', $channel);` (`$channel` may be `null` if none of the lookups succeed)
- `$request->attributes->set('channel_id', $channel?->id);`

There is no abort in this middleware when the channel is missing.

### `SetLocaleFromRequest` (`App\Http\Middleware\SetLocaleFromRequest`)

Uses `App\Services\ChannelLocaleService` and the already-bound `currentChannel` when present.

Supported codes: `ChannelLocaleService::codesForChannel($channel)`. If that array is empty, `config('app.supported_locales', [config('app.locale', 'en')])`. Codes are normalized (underscore → hyphen, language lowercased, optional region uppercased).

Candidate order:

1. `?locale=` or header `X-Locale` (query wins over header when both are present because of `??`)
2. `Accept-Language` (q-values; `*` ignored)
3. `ChannelLocaleService::defaultCodeForChannel($channel)` or `config('app.locale', config('app.fallback_locale', 'en'))`

Match: exact normalized code, else language prefix before `-`.

Then `app()->setLocale($candidate)` and `$request->attributes->set('api_locale', $candidate)`.

### Sitemap middleware order

`routes/web.php` applies `['locale.api', 'channel.api']` (locale **before** channel) to:

- `GET /sitemap.xml`
- `GET /sitemaps/products.xml`
- `GET /sitemaps/categories.xml`
- `GET /sitemaps/pages.xml`

`App\Http\Controllers\SitemapController` reads `app('currentChannel')` and `app()->getLocale()`. `channelId()` aborts 400 with `Channel not resolved` when the bound channel has no `id`. `baseUrl()` uses `currentChannel->domain` when non-empty, else `config('app.url')`.

Because `locale.api` runs first here, `SetLocaleFromRequest` sees `currentChannel` only if something else already bound it.

## Config hydration (`App\Providers\AppServiceProvider`)

Runs in `boot()` only when `!app()->runningInConsole()`. Exceptions are swallowed (fallback remains `config/locales.php`).

`Cache::remember('locales.active', 3600, ...)` loads `Locale::where('is_active', true)->get()`.

If the collection is not empty:

- `config(['locales' => keyed-by-code map of name + flag])`
- `config(['app.supported_locales' => array_keys($formatted)])`
- if a row has `is_default`, `config(['app.locale' => $default])`

`config/app.php` file defaults: `'locale' => env('APP_LOCALE', 'en')`, `'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en')`. There is no `supported_locales` key in `config/app.php`; that key is set only by this hydration.

### View composer (`view()->composer('*', ...)`)

Always: `$view->with('availableLocales', config('locales'))`.

If authenticated:

- `userChannels` = `$user->channels`
- `currentChannel` = `$user->currentChannel`
- `channelLocales` = `channel_locales($currentChannel?->id)`
- if `channelLocales !== []`, overwrite `availableLocales` with `channelLocales`

## Channel–locale fallback rules (`ChannelLocaleService`)

`localesForChannel(?Channel)`:

- No channel → `globalLocaleMap()`
- Channel with at least one **active** assigned locale → map of those rows
- Channel with empty assignment → `globalLocaleMap()`

`globalLocaleMap()`:

- If `config('locales', [])` is non-empty, return that array
- Else active `Locale` rows ordered by `is_default` desc, then `code`

Admin channel create/edit with empty `locale_ids` calls `attachAllActiveLocales()`, which **syncs all active locales** onto the pivot (not an empty pivot). Empty pivot is still the runtime fallback in the service if the pivot is empty for another reason.

## Caches

| Key | TTL | Written | Forgotten |
| --- | --- | --- | --- |
| `locales.active` | 3600 | `AppServiceProvider` | `LocaleController::flushLocaleCaches()`, `App\Services\System\SystemCacheService::clearStorefrontDataCaches()` |
| `app:active_locales` | 300 | `Locale::activeCodes()` | same as above |
| `api.locales.v1.{channelId}` | 3600 | `App\Http\Controllers\Api\V1\LocaleController` via `ChannelLocaleService::apiCacheKey()` | `ChannelLocaleService::flushApiCache()`, `SystemCacheService` |
| `api.locales.v1` | — | No `Cache::remember` for this exact key in this module | `flushApiCache()` and `SystemCacheService` call `Cache::forget('api.locales.v1')` |

`flushApiCache(?int $channelId)`:

- always forgets `api.locales.v1`
- if `$channelId` given, forgets `api.locales.v1.{id}`
- else forgets that key for every `Channel` id

`Api\V1\LocaleController` uses `$channel?->id ?? 0`, so a missing channel caches under `api.locales.v1.0`.

## Translation files vs DB locales

`TranslationController` reads/writes `lang_path("{locale}/translations.php")` for `array_keys(config('locales'))`.

On HTTP, after hydration, those keys are **active DB locales**, not the `lang/` directory list.

On disk (this repo): `lang/al`, `lang/de`, `lang/en`, `lang/fr`, `lang/it`, `lang/sq` — each contains only `translations.php`.

`config/locales.php` ships a single entry: `en` → name `English`, flag `us_flag.jpg`.

`LocaleSeeder` upserts `sq` (default, flag `albania_flag.jpg`) and `en` (flag `us_flag.jpg`). It does not seed `al`, `de`, `fr`, or `it`.

## Default channel code

`ChannelSeeder` upserts `code = default`.

`ResolveApiChannel` fallback is `code = 'default'`.

`ChannelController::delete` refuses deletion when `$channel->code === 'default'`.
