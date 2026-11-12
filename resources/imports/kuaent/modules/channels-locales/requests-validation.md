# Requests and validation

There are **no** Form Request classes for this module (`app/Http/Requests` has no Channel/Locale/Translation request). Controllers call `$request->validate(...)` or read input without a Form Request.

## `App\Http\Controllers\Admin\ChannelController`

### `create` (POST) and `edit` (POST)

| Field | Rules |
| --- | --- |
| `name` | `required\|string\|max:255` |
| `code` | `required\|string\|max:100\|unique:channels,code` (edit: `unique:channels,code,{id}`) |
| `domain` | `nullable\|string` |
| `locale_ids` | `nullable\|array` |
| `locale_ids.*` | `integer\|exists:locales,id` |
| `default_locale_id` | `nullable\|integer\|exists:locales,id` |

`Channel::create` / `update` use `$request->only('name', 'code', 'domain')` after validation.

Empty `locale_ids` (missing or `[]`) → `ChannelLocaleService::attachAllActiveLocales`. `default_locale_id` is not required to be a member of `locale_ids` at validation time; `syncChannelLocales` replaces an invalid/missing default with the first selected id.

`switch` and `delete` have no Form validation. Switch: 403 if the user is not attached to the channel. Delete: blocked when `code === 'default'`.

## `App\Http\Controllers\Admin\LocaleController`

### `store` and `update`

| Field | Rules |
| --- | --- |
| `code` | `required\|string\|max:10\|unique:locales,code` (update: unique ignore current id) |
| `name` | `required\|string\|max:255` |
| `flag` | `nullable\|string\|max:255` |

Not in `validate()`:

- `is_active` → `$request->boolean('is_active')`
- `is_default` → `$request->boolean('is_default')`

After validate, `code` is rewritten: `strtolower(str_replace('_', '-', $data['code']))`.

`switch`: no `validate()`. Rejects with 400 when the normalized code is not in `channel_locale_codes()`.

`destroy`: no `validate()`. Rejects default locale with a flash error.

## `App\Http\Controllers\Admin\TranslationController`

### `store`

| Field | Rules |
| --- | --- |
| `key` | `required\|string` |

`translations` is not validated. The method does `foreach ($request->translations as ...)`.

### `update`

No `validate()`. Uses `$request->key` and `$request->translations`.

### `destroy`

No `validate()`. `{key}` comes from the route.

Locales written are whatever keys appear in `$request->translations` (store/update) or `array_keys(config('locales'))` (destroy). There is no `exists:locales,code` check.

## `App\Http\Controllers\Api\V1\LocaleController`

`index` has no request validation. Channel and locale come from middleware.

## Related validation outside this module

`App\Http\Controllers\Api\V1\AuthController::register` includes `'locale' => ['nullable','string','max:10']`.

`App\Http\Controllers\Admin\UserController` syncs `$request->channels` onto `User::channels()` but does **not** include `channels` in its `validate()` arrays.
