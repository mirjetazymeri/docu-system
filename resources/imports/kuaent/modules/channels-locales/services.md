# Services and helpers

## `App\Services\ChannelLocaleService`

Path: `app/Services/ChannelLocaleService.php`

Not registered as a singleton in `AppServiceProvider`; resolved from the container as a default class binding.

Uses `App\Models\Channel`, `App\Models\Locale`, `Illuminate\Support\Facades\Cache`.

### `localesForChannel(?Channel $channel): array`

Returns `code => ['name' => ..., 'flag' => ...]`.

- `$channel === null` → `globalLocaleMap()`
- Assigned **active** locales non-empty → `mapLocales($assigned)`
- Assigned empty → `globalLocaleMap()`

`assignedLocales()`: if relation `locales` is loaded, filter `is_active`; else query `locales()` where `locales.is_active`, order `channel_locale.is_default` desc, `locales.code` asc.

### `codesForChannel(?Channel $channel): array`

`array_keys(localesForChannel($channel))`.

### `defaultCodeForChannel(?Channel $channel): string`

If channel present: assigned active locales, first with `pivot.is_default`, else first assigned; use its `code` when set.

Else `Locale::query()->where('is_default', true)->value('code')` or `config('app.locale', 'en')`, cast to string.

### `syncChannelLocales(Channel $channel, array $localeIds, ?int $defaultLocaleId = null): void`

Unique positive int ids. Empty list: `locales()->detach()`, `flushApiCache($channel->id)`, return.

If `$defaultLocaleId` is missing or not in `$localeIds`, uses `$localeIds[0]`.

`sync` with pivot `is_default` true only for that id. Then `flushApiCache($channel->id)`.

### `attachAllActiveLocales(Channel $channel): void`

Active locales ordered `is_default` desc, `code` asc. Empty → return (no detach). Default id = global default among that set, else first. Delegates to `syncChannelLocales`.

### `flushApiCache(?int $channelId = null): void`

See [architecture.md](architecture.md) cache table.

### `apiCacheKey(int $channelId): string`

`"api.locales.v1.{$channelId}"`.

### `globalLocaleMap()` (private)

If `config('locales', []) !== []`, return it. Else map active `Locale` rows.

On HTTP after `AppServiceProvider` hydration, `config('locales')` is usually non-empty, so the DB query branch is unused in that case.

## Helpers (`app/helpers.php`)

Autoloaded via `composer.json` `autoload.files`: `app/helpers.php`.

### `current_channel()`

Returns `app('currentChannel')` if `app()->bound('currentChannel')`, else `null`.

### `channel_locales(?int $channelId = null): array`

Resolves `ChannelLocaleService`.

- If `$channelId` is truthy: `Channel::query()->find($channelId)` then `localesForChannel` (missing id → `localesForChannel(null)`).
- Else `$channel = current_channel()`, or if that is empty and `auth()->check()`, `auth()->user()->currentChannel`.
- `localesForChannel($channel)`.

### `channel_locale_codes(?int $channelId = null): array`

`array_keys(channel_locales($channelId))`.

Call sites of these helpers (incoming) are listed in [dependencies.md](dependencies.md).

## Related flush outside this class

`App\Services\System\SystemCacheService::clearStorefrontDataCaches()` forgets `locales.active`, `app:active_locales`, `api.locales.v1`, then `app(ChannelLocaleService::class)->flushApiCache()`. It also iterates `Channel::query()->pluck('id')` for per-channel settings cache via `SettingService` (settings module).
