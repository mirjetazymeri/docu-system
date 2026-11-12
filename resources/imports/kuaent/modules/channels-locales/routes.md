# Routes (web)

File: `routes/web.php`. Middleware aliases: `bootstrap/app.php`.

## Aliases (`bootstrap/app.php`)

| Alias | Class |
| --- | --- |
| `set.user.locale` | `App\Http\Middleware\SetUserLocale` |
| `locale.api` | `App\Http\Middleware\SetLocaleFromRequest` |
| `channel.api` | `App\Http\Middleware\ResolveApiChannel` |
| `check.permission` | `App\Http\Middleware\AutoAuthorizePermission` |
| `role` | `App\Http\Middleware\RoleMiddleware` |

Web group extra: `$middleware->appendToGroup('web', \App\Http\Middleware\SetCurrentChannel::class)`.

## Sitemap (web, API-style middleware)

Middleware: `locale.api`, `channel.api` (locale first).

| Method | URI | Action |
| --- | --- | --- |
| GET | `/sitemap.xml` | `App\Http\Controllers\SitemapController@index` |
| GET | `/sitemaps/products.xml` | `products` |
| GET | `/sitemaps/categories.xml` | `categories` |
| GET | `/sitemaps/pages.xml` | `pages` |

## Admin group

Middleware: `auth`, `verified.if-required`, `set.user.locale`, `role:Admin`. Prefix `admin`. Name prefix `admin.`.

### Channels

| Method | URI | Name | Controller |
| --- | --- | --- | --- |
| GET | `admin/channels` | `admin.channels.index` | `ChannelController@index` |
| GET, POST | `admin/channels/create` | `admin.channels.create` | `create` |
| GET, POST | `admin/channels/edit/{id}` | `admin.channels.edit` | `edit` |
| DELETE | `admin/channels/delete/{id}` | `admin.channels.delete` | `delete` |
| GET | `admin/channels/switch/{channel}` | `admin.channels.switch` | `switch` |

The switch route uses a fully qualified class name in `web.php`; it is the same `App\Http\Controllers\Admin\ChannelController`.

### Locales

`Route::resource('locales', LocaleController::class)` → `App\Http\Controllers\Admin\LocaleController`.

Laravel resource names (prefix `admin.`):

| HTTP | URI | Name | Implemented |
| --- | --- | --- | --- |
| GET | `admin/locales` | `admin.locales.index` | yes |
| GET | `admin/locales/create` | `admin.locales.create` | yes |
| POST | `admin/locales` | `admin.locales.store` | yes |
| GET | `admin/locales/{locale}` | `admin.locales.show` | **no method** |
| GET | `admin/locales/{locale}/edit` | `admin.locales.edit` | yes |
| PUT/PATCH | `admin/locales/{locale}` | `admin.locales.update` | yes |
| DELETE | `admin/locales/{locale}` | `admin.locales.destroy` | yes |

Extra:

| Method | URI | Name |
| --- | --- | --- |
| GET | `admin/locale/{locale}` | `admin.locale.switch` |

`{locale}` on switch is a **code string**, not a `Locale` model.

### Translations

| Method | URI | Name | Action |
| --- | --- | --- | --- |
| GET | `admin/translations` | `admin.translations.index` | `index` |
| POST | `admin/translations` | `admin.translations.store` | `store` |
| POST | `admin/translations/update` | `admin.translations.update` | `update` |
| DELETE | `admin/translations/{key}` | `admin.translations.destroy` | `destroy` |

`{key}` is a translation key string (may include dots).

## Other web groups using `set.user.locale`

Not channel CRUD, but they run `SetUserLocale`:

- Profile: `profile`, `profile` PUT, `profile/password` PUT
- Customer: prefix `customer`, `role:Customer`
- Sales Specialist: prefix `sales-specialist`, `role:Sales Specialist`

## API routes

See [api.md](api.md). Defined in `routes/api.php`.
