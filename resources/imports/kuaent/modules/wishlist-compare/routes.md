# Routes

Back to [README](README.md). Endpoint contracts: [api.md](api.md).

## Group

Source: `routes/api.php`

Laravel registers `routes/api.php` with the default `/api` prefix (`bootstrap/app.php` `withRouting(api: …)`). This file then wraps storefront routes in:

```php
Route::prefix('v1')->middleware(['channel.api', 'locale.api'])->group(function () {
```

Aliases (`bootstrap/app.php`):

| Alias | Class |
| ----- | ----- |
| `channel.api` | `App\Http\Middleware\ResolveApiChannel` |
| `locale.api` | `App\Http\Middleware\SetLocaleFromRequest` |

Wishlist and compare routes sit in that public `v1` group. They are **not** inside the later `Route::middleware('auth:sanctum')` group. Controllers still call `$request->user('sanctum')` when a token is present.

None of these routes have a `->name(…)`.

No web, admin, webhook, or console routes belong to this module.

## API

| Method | URI | Name | Controller | Middleware | Permission |
| ------ | --- | ---- | ---------- | ---------- | ---------- |
| POST | `/api/v1/wishlist` | — | `WishlistController@create` | `channel.api`, `locale.api` | none |
| GET | `/api/v1/wishlist` | — | `WishlistController@show` | `channel.api`, `locale.api` | none |
| DELETE | `/api/v1/wishlist` | — | `WishlistController@clear` | `channel.api`, `locale.api` | none |
| POST | `/api/v1/wishlist/items` | — | `WishlistController@addItem` | `channel.api`, `locale.api` | none |
| DELETE | `/api/v1/wishlist/items/{id}` | — | `WishlistController@removeItem` | `channel.api`, `locale.api` | none |
| POST | `/api/v1/compare` | — | `CompareController@create` | `channel.api`, `locale.api` | none |
| GET | `/api/v1/compare` | — | `CompareController@show` | `channel.api`, `locale.api` | none |
| DELETE | `/api/v1/compare` | — | `CompareController@clear` | `channel.api`, `locale.api` | none |
| POST | `/api/v1/compare/items` | — | `CompareController@addItem` | `channel.api`, `locale.api` | none |
| DELETE | `/api/v1/compare/items/{id}` | — | `CompareController@removeItem` | `channel.api`, `locale.api` | none |

`{id}` is the **item** primary key (`wishlist_items.id` or `compare_items.id`).

## Channel middleware (this module's use)

`ResolveApiChannel` sets `app('currentChannel')` from, in order: query `channel`, header `X-Channel`, authenticated user's `currentChannel`, else `Channel` with `code = default`. It may set a null instance if none of those resolve. Controllers then `abort(400, 'Channel not resolved')` when `id` is missing.

## Locale middleware (this module's use)

`SetLocaleFromRequest` sets `app()->setLocale(…)`. Wishlist/compare resources do not call `Product::translation()`; they use `translations->first()`. Locale still applies to anything else that reads `app()->getLocale()` during the request.

## Observed: unused import

`routes/api.php` line 35:

```php
use App\Http\Controllers\Api\V1\ProductCompareController;
```

No route references `ProductCompareController`. The class file does not exist under `app/Http/Controllers`. Compare routes use `App\Http\Controllers\Api\V1\CompareController`. This is a dead import (unused alias; not autoloaded until referenced).

`routes/api.php` also imports `Psy\Output\ProcOutputPager` (unrelated; unused in the wishlist/compare section).
