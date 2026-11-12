# API

## Group

File: `routes/api.php`.

```php
Route::prefix('v1')->middleware(['channel.api', 'locale.api'])->group(...)
```

Default Laravel API prefix: `api`. Channel is resolved **before** locale.

This module’s own endpoint:

| Method | URI | Controller | Auth |
| --- | --- | --- | --- |
| GET | `/api/v1/locales` | `App\Http\Controllers\Api\V1\LocaleController@index` | none (same public group as catalog) |

Every other `v1` route in that file also runs `channel.api` and `locale.api`. Those controllers are not part of this module; they consume `app('currentChannel')` and `app()->getLocale()`. See [dependencies.md](dependencies.md).

There are no OpenAPI annotations on `App\Http\Controllers\Api\V1\LocaleController`. A request example exists in `postman_collection.json` for `{{base_url}}/api/v1/locales`.

## Request inputs (middleware)

### Channel (`App\Http\Middleware\ResolveApiChannel`)

| Source | Lookup |
| --- | --- |
| Query `channel` | `Channel.where('code', $slug)` |
| Header `X-Channel` | same |
| Authenticated user | `User::currentChannel` |
| Fallback | `Channel.where('code', 'default')` |

Invalid codes are skipped (not 404). If all steps fail, `currentChannel` is `null`.

### Locale (`App\Http\Middleware\SetLocaleFromRequest`)

| Source | Notes |
| --- | --- |
| Query `locale` | Preferred over header if both set |
| Header `X-Locale` | |
| Header `Accept-Language` | q-values; `*` ignored |
| Fallback | channel default code, then `config('app.locale')` |

Supported codes are the channel’s locale codes from `ChannelLocaleService`, else `config('app.supported_locales')`.

## `GET /api/v1/locales` response

`App\Http\Controllers\Api\V1\LocaleController::index`:

1. `$channel = app()->bound('currentChannel') ? app('currentChannel') : null`
2. Cache key `ChannelLocaleService::apiCacheKey($channel?->id ?? 0)`, TTL 3600
3. Cached payload (without `current`):
   - `codesForChannel($channel)`
   - `defaultCodeForChannel($channel)`
   - Active `Locale` rows, filtered `whereIn('code', $codes)` when `$codes !== []`
   - Order: `is_default` desc, `code` asc
   - Each item: `code`, `name`, `flag`, `is_default` (true when `code === $defaultCode`, not the `locales.is_default` column)
   - `default`: `$defaultCode`
4. JSON:

```json
{
  "success": true,
  "data": {
    "locales": [
      {
        "code": "sq",
        "name": "Shqip",
        "flag": "albania_flag.jpg",
        "is_default": true
      }
    ],
    "default": "sq",
    "current": "en"
  }
}
```

`current` is `app()->getLocale()` and is **not** stored in the cache array (merged after `Cache::remember`).

## API register and `users.locale`

`App\Http\Controllers\Api\V1\AuthController::register` (same `v1` middleware group) validates optional `locale` (`nullable|string|max:10`) and stores it on the user. That is auth-module code consuming the `users.locale` column. `App\Support\AuthUserPayload` does **not** return `locale` or channel.
