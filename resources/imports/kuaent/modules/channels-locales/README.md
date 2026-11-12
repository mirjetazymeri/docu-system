# Channels & locales

Sales channels, locale records, admin language/channel switching, PHP translation files, and API channel+locale middleware for `C:\laragon\www\TestDocu-admin`.

This module is not a Composer package. Its types live in `App\` and are referenced by catalog, cart, CMS, checkout, and admin screens.

## Purpose

- Persist **sales channels** (`App\Models\Channel`) with `name`, `code`, `domain`.
- Persist **locales** (`App\Models\Locale`) and assign them to channels via `channel_locale` (`is_default` pivot).
- Assign users to channels (`channel_user`) and store `users.current_channel_id` / `users.locale`.
- Bind `currentChannel` on web and API requests.
- Set `app()->getLocale()` for admin (user/session) and storefront API (query/header/`Accept-Language`).
- Expose `GET /api/v1/locales` for the resolved channel.
- Edit `lang/{locale}/translations.php` from the admin UI.

## Owned vs shared files

Owned by this module:

| Path | Role |
| --- | --- |
| `app/Models/Channel.php` | Channel Eloquent model |
| `app/Models/Locale.php` | Locale Eloquent model |
| `app/Services/ChannelLocaleService.php` | Channel↔locale maps, sync, API cache flush |
| `app/Http/Controllers/Admin/ChannelController.php` | Admin channel CRUD + switch |
| `app/Http/Controllers/Admin/LocaleController.php` | Admin locale CRUD + switch |
| `app/Http/Controllers/Admin/TranslationController.php` | Admin translation file editor |
| `app/Http/Controllers/Api/V1/LocaleController.php` | `GET /api/v1/locales` |
| `app/Http/Middleware/ResolveApiChannel.php` | Alias `channel.api` |
| `app/Http/Middleware/SetLocaleFromRequest.php` | Alias `locale.api` |
| `app/Http/Middleware/SetUserLocale.php` | Alias `set.user.locale` |
| `app/Http/Middleware/SetCurrentChannel.php` | Appended to the `web` group |
| `config/locales.php` | File fallback for `config('locales')` |
| `lang/{al,de,en,fr,it,sq}/translations.php` | Translation arrays |
| `database/seeders/ChannelSeeder.php` | Default channel + user attach |
| `database/seeders/LocaleSeeder.php` | `sq` (default) and `en` |
| `resources/views/admin/channels/` | Channel Blade UI |
| `resources/views/admin/locales/` | Locale Blade UI |
| `resources/views/admin/translations/` | Translation Blade UI |

Shared files that contain this module’s wiring (do not treat the whole file as this module):

| Path | What this module uses |
| --- | --- |
| `app/helpers.php` | `current_channel()`, `channel_locales()`, `channel_locale_codes()` |
| `app/Providers/AppServiceProvider.php` | HTTP hydration of `config('locales')`; view composer |
| `bootstrap/app.php` | Middleware aliases and `web` append of `SetCurrentChannel` |
| `bootstrap/providers.php` | Registers `App\Providers\AppServiceProvider` |
| `routes/web.php` | Admin channel/locale/translation routes; sitemap middleware |
| `routes/api.php` | `v1` group middleware + `GET locales` |
| `app/Models/User.php` | `locale`, `current_channel_id`, `channels()`, `currentChannel()` |
| `database/seeders/DatabaseSeeder.php` | Calls `ChannelSeeder` then `LocaleSeeder` |
| `database/seeders/ModulePermissionSeeder.php` | Modules `Channels`, `Locales`, `Translations` |

## Docs in this folder

| File | Contents |
| --- | --- |
| [architecture.md](architecture.md) | Request pipelines, resolution order, caches, hydration |
| [models.md](models.md) | `Channel`, `Locale`, `User` fields/relations used here |
| [database.md](database.md) | Tables, migrations, seeders |
| [controllers.md](controllers.md) | Admin + API controllers, views, permissions |
| [services.md](services.md) | `ChannelLocaleService` and helpers |
| [routes.md](routes.md) | `routes/web.php` and middleware aliases |
| [api.md](api.md) | `GET /api/v1/locales` and API middleware |
| [requests-validation.md](requests-validation.md) | Inline validation (no Form Request classes) |
| [dependencies.md](dependencies.md) | Incoming/outgoing references from code |
| [tests.md](tests.md) | Test inventory |

## Permissions

Seeded in `database/seeders/ModulePermissionSeeder.php` as `{resource}_{action}`:

- Module `Channels`: `channel_view`, `channel_create`, `channel_edit`, `channel_delete`
- Module `Locales`: `locale_view`, `locale_create`, `locale_edit`, `locale_delete`
- Module `Translations`: `translation_view`, `translation_create`, `translation_edit`, `translation_delete`

Admin controllers apply these via `check.permission` (`App\Http\Middleware\AutoAuthorizePermission`). Blade uses `@can` / `@canany` on the same permission names.

## Runtime container keys

| Key | Bound by | Consumers |
| --- | --- | --- |
| `currentChannel` | `App\Http\Middleware\SetCurrentChannel`, `App\Http\Middleware\ResolveApiChannel` (also rebound by other modules; see [dependencies.md](dependencies.md)) | Helpers, API, catalog, cart |
| `currentChannelId` | Not bound by this module’s middleware | `App\Services\Checkout\CartService::currentChannelId()` if already bound; tests bind it |
| Request attribute `channel_id` | `ResolveApiChannel` | Optional stash of `$channel?->id` |
| Request attribute `api_locale` | `SetLocaleFromRequest` | Optional stash of the chosen locale code |
