# Reviews — Permissions and authorization

Module README: [README.md](README.md)

## Seeded admin permissions

Source: `database/seeders/ModulePermissionSeeder.php`

Module display name **`Reviews`**. Resource prefix `review`, actions `view`, `edit`, `delete` (no `create`).

| Permission name | Guard in seeder | Used for |
| --------------- | --------------- | -------- |
| `review_view` | `web` | Admin `index`; sidebar `@can('review_view')` |
| `review_edit` | `web` | Admin `edit`, `update`, `setStatus`, `bulk`; Blade bulk approve/reject/spam; row edit/approve; API status change via `$user->can('review_edit')`; policy `update` fallback |
| `review_delete` | `web` | Admin `delete`; Blade delete controls; policy `delete` fallback |

`DatabaseSeeder` calls `ModulePermissionSeeder` then `SuperAdminSeeder` (Super Admin sync is documented in users-access-control).

There is **no** permission named `manage_reviews` in this seeder.

## Admin HTTP enforcement

1. Route group: `auth`, `verified.if-required`, `set.user.locale`, `role:Admin` (`RoleMiddleware` → `$user->hasAnyRole('Admin')`).
2. Controller: `check.permission:{name}` → `App\Http\Middleware\AutoAuthorizePermission`.
   - No user → redirect `login`.
   - `isSuperAdmin()` → allow.
   - `hasAccessLevelPermission($permission)` → allow.
   - Else abort 403 `Access denied: missing permission [{permission}] via access level.`

`authorize()` on admin FormRequests is `true`; they do not re-check `review_edit`.

## Blade

| File | Gates |
| ---- | ----- |
| `resources/views/layouts/components/main-sidebar.blade.php` | `review_view`; catalog parent `@canany` includes `review_view` |
| `resources/views/admin/reviews/index.blade.php` | `@canany(['review_edit','review_delete'])` bulk UI; `@can('review_edit')` approve/reject/spam; `@can('review_delete')` bulk delete |
| `resources/views/admin/reviews/actions.blade.php` | same canany; edit/approve vs delete |
| `resources/views/admin/reviews/edit.blade.php` | `@can('review_delete')` delete button |

`@can` / `$user->can()` go through Laravel Gate. `AuthServiceProvider::boot()` `Gate::before`: Super Admin → true; `hasAccessLevelPermission($ability)` → true; else `null` (Spatie/other continues).

## Policy (API authenticated users)

Source: `app/Policies/ProductReviewPolicy.php`

Registered in `app/Providers/AuthServiceProvider.php`:

```text
ProductReview::class => ProductReviewPolicy::class
```

Provider is listed in `bootstrap/providers.php`.

| Ability | Rule |
| ------- | ---- |
| `update` | `$user->id === $review->user_id` **or** `$user->can('review_edit')` |
| `delete` | `$user->id === $review->user_id` **or** `$user->can('review_delete')` |

No `view` / `create` policy methods. Guest create/update/delete do not use the policy; guests use owner token in the controller.

Policy is only invoked when `$req->user()` is truthy. Guests with a token never hit `authorize()`.

## Storefront create

`StoreProductReviewRequest::authorize()` is **not** a Spatie permission: logged-in **or** guests enabled.

## Guest owner token

Not a permission. See `guestTokenValid()` in [controllers.md](controllers.md). Token is stored in `owner_edit_token` (not hashed). Compare uses `hash_equals`.

## Auth module touchpoints

- `User` must be authenticatable for policy and `$req->user()`.
- `User` uses `Laravel\Sanctum\HasApiTokens` (auth module). Review routes are still outside the `auth:sanctum` group.
- Admin session auth is Fortify/`auth` middleware (auth module).
