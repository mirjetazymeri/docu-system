# Wishlist and Compare

## Purpose

Storefront API for **wishlist** and **compare** lists: guests and Sanctum-authenticated users can create or attach a channel-scoped list, add/remove products, and clear items. There is no admin UI, no dedicated service layer, and no FormRequest classes.

## Responsibilities

- Persist per-channel guest lists keyed by `session_token` and per-user lists keyed by `user_id`.
- Merge a guest list into the authenticated user's active list when a guest token is present.
- Add/remove products by `product_id` / item id; keep denormalized `items_count` in sync.
- Serialize list items with product translation, image, and channel price snapshots via API resources.

## Source Code Locations

```text
app/Http/Controllers/Api/V1/WishlistController.php
app/Http/Controllers/Api/V1/CompareController.php
app/Http/Resources/Api/V1/WishlistResource.php
app/Http/Resources/Api/V1/CompareResource.php
app/Models/Wishlist.php
app/Models/WishlistItem.php
app/Models/Compare.php
app/Models/CompareItem.php
routes/api.php
database/migrations/2026_05_10_172102_create_wishlists_table.php
database/migrations/2026_05_10_172214_create_wishlist_items_table.php
database/migrations/2026_05_10_174236_create_compares_table.php
database/migrations/2026_05_10_174327_create_compare_items_table.php
```

Related (not owned by this module):

```text
docs-storefront/wishlist/README.md
docs-storefront/compare/README.md
config/settings.php   # header.main.show_wishlist — storefront setting; not read by these controllers
```

## Main Components

| Type | Class | Responsibility |
| ---- | ----- | -------------- |
| Controller | `App\Http\Controllers\Api\V1\WishlistController` | Wishlist create/show/add/remove/clear and guest-to-user merge |
| Controller | `App\Http\Controllers\Api\V1\CompareController` | Compare create/show/add/remove/clear and guest-to-user merge |
| Resource | `App\Http\Resources\Api\V1\WishlistResource` | JSON payload for a wishlist and its items |
| Resource | `App\Http\Resources\Api\V1\CompareResource` | JSON payload for a compare list and its items |
| Model | `App\Models\Wishlist` | Header row: channel, user, token, status, item count |
| Model | `App\Models\WishlistItem` | Product line on a wishlist |
| Model | `App\Models\Compare` | Header row: channel, user, token, status, item count |
| Model | `App\Models\CompareItem` | Product line on a compare list |

**Not present in this module:** services, repositories, actions, FormRequests, policies, jobs, events, listeners, notifications, observers, factories, seeders, Artisan commands, web/admin routes.

## Module Dependencies

This module depends on:

- [catalog-products](../catalog-products/README.md) — `Product` (and loaded `translations`, `images`, `prices`); `exists:products,id`; `product_id` foreign keys
- [channels-locales](../channels-locales/README.md) — `channel.api` / `locale.api` middleware; `app('currentChannel')`; `channel_id` foreign keys
- [auth](../auth/README.md) — optional identity via `$request->user('sanctum')` (routes are **not** behind `auth:sanctum`)
- [users-access-control](../users-access-control/README.md) — `user_id` to `users.id` (`User` has no inverse wishlist/compare relations)

This module is used by:

- Storefront HTTP clients (not implemented in this Laravel `app/` tree). Related client notes live in `docs-storefront/wishlist/` and `docs-storefront/compare/`.
- No other PHP classes under `app/` import these models besides this module's controllers and resources.

See [dependencies.md](dependencies.md).

## Entry Points

| Kind | Location | Notes |
| ---- | -------- | ----- |
| API | `POST/GET/DELETE /api/v1/wishlist`, `POST/DELETE /api/v1/wishlist/items…` | Public `v1` group |
| API | `POST/GET/DELETE /api/v1/compare`, `POST/DELETE /api/v1/compare/items…` | Public `v1` group |
| Web / admin | None | `routes/web.php` has no wishlist/compare routes |
| Commands / schedule / jobs / events | None | — |

See [routes.md](routes.md) and [api.md](api.md).

## Important Business Rules

Proven by controller/model/migration code:

- Lists are scoped to `app('currentChannel')->id`. If the channel is missing, the controller calls `abort(400, 'Channel not resolved')`.
- Guest identity: header `X-Wishlist-Token` or query `wishlist_token` (compare: `X-Compare-Token` / `compare_token`). If missing on create/show/add/remove, a new UUID is generated and used as the guest token.
- Authenticated identity: `$request->user('sanctum')?->id`. These routes do **not** use `auth:sanctum` middleware; a Bearer token is optional.
- User lists are the latest row with `status = 'active'` for that `channel_id` + `user_id` (`latest('updated_at')`, then `latest('id')`).
- Guest lists are `firstOrCreate` on `channel_id` + `session_token` + `status = 'active'`.
- New **user** lists are created with `session_token = null`. New **guest** lists store the UUID in `session_token`.
- When an authenticated request includes a guest token that matches an active guest list on the same channel, items are merged into the user's list (`firstOrCreate` per `product_id`); the guest list's items are deleted and the guest header row is deleted. If the user had no list, the guest row is assigned `user_id`.
- Adding a product uses `firstOrCreate` on `(list_id, product_id)` and matches the unique constraint on item tables. Re-adding the same product does not create a second row.
- `items_count` is rewritten from `$list->items()->count()` after add, remove, and merge.
- `clear` deletes items and sets `items_count` to `0`. Wishlist `clear` also sets `status` to `'active'`. Compare `clear` does not change `status`. The header row is **not** deleted.
- Guest `clear` without a token returns success and does nothing (`No active wishlist/compare to clear.`). Authenticated `clear` looks up the user's active list only (it does not require a guest token).
- Item uniqueness is per list + product. There is no quantity field.
- Resources pick `$product->translations->first()`, not `Product::translation()` / current locale. `locale.api` still runs on the route group and sets `app()->getLocale()`, but these resources do not use it for name/slug/description.
- `config/settings.php` key `header.main.show_wishlist` (public boolean, default `true`) is not read by `WishlistController` or `CompareController`. There is no `show_compare` setting in that config file.

## Observed: dead import

`routes/api.php` contains `use App\Http\Controllers\Api\V1\ProductCompareController;` and does not reference that class on any route. No `ProductCompareController.php` exists under `app/`. PHP does not autoload an unused `use` alias, so this does not by itself fatal at runtime. Storefront compare is implemented by `CompareController`.

## Documentation Index

| File | Contents |
| ---- | -------- |
| [models.md](models.md) | Eloquent models and relations |
| [database.md](database.md) | Tables, FKs, unique constraints |
| [controllers.md](controllers.md) | API controller behavior |
| [routes.md](routes.md) | Route table and middleware |
| [api.md](api.md) | Endpoints, headers, payloads |
| [requests-validation.md](requests-validation.md) | Inline validation (no FormRequests) |
| [dependencies.md](dependencies.md) | Incoming/outgoing module deps |
| [tests.md](tests.md) | Test coverage (none found) |
