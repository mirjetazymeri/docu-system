# Sales specialist — permissions

This desk is gated by a **Spatie role name**, not by access-level permission strings (`order_view`, etc.).

## Route gate

`App\Http\Middleware\RoleMiddleware` (`alias` `role` in `bootstrap/app.php`):

1. Not authenticated → redirect `login`.
2. `$user->hasAnyRole(...$roles)` — here a single role: **`Sales Specialist`**.
3. Else `abort(403, 'Unauthorized access.')`.

`hasAnyRole` is Spatie `HasRoles` on `App\Models\User`. Guard used when seeding the role is `web`.

Admin `check.permission:*` (`AutoAuthorizePermission`) is **not** applied to `/sales-specialist/*`. Super Admin access-level bypass therefore does **not** open this prefix; the user still needs the Spatie role (or they 403).

## Seeded role and user

| Source | What |
|---|---|
| `database/seeders/RoleSeeder.php` | `Role::firstOrCreate` name `Sales Specialist`, `guard_name` `web`. Assigns that role to **user id 4** if the row exists. |
| `database/seeders/DatabaseSeeder.php` | `User::firstOrCreate` email `sales@sales.com`, name `Sales Specialist`, `email_verified_at` set. Password value is in the seeder file; not repeated here. |

Create-customer uses a **different** role: `Customer` (`CustomerController::store`).

## Order ownership (second gate)

List/dashboard queries: `Order::ownedBySalesSpecialist((int) auth()->id())` → `where sales_specialist_id = $userId`.

`OrderController::show`: after route-model binding, `authorizeOwnership()` compares `sales_specialist_id` to `auth()->id()`. Mismatch → **403** `You do not have access to this order.`

There is no `OrderPolicy` and no `$this->authorize()` call.

Guest orders placed by the specialist still have `sales_specialist_id` set; `user_id` may be null. They appear in “My Orders”.

## Named permissions

`database/seeders/ModulePermissionSeeder.php` has **no** sales-specialist module or permission names.

Blade `@can(...)` is not used on specialist sidebar items; visibility is `hasRole('Sales Specialist')`.

## Customer search/create

Search: `User::role('Customer')` only.

Create: assigns Spatie role `Customer` and group slug `customer`. The acting specialist does not receive extra permissions from that.

## Login vs `/` redirect

| Path | Sales Specialist |
|---|---|
| Fortify `LoginResponse` (`FortifyServiceProvider`) | After Customer check: `sales-specialist.dashboard` |
| `GET /` (`routes/web.php`) | After Admin check: `sales-specialist.dashboard` |

A user with **both** Admin and Sales Specialist hits Admin first on `GET /`. LoginResponse checks Customer before Sales Specialist and never checks Admin by name (non-Customer, non-specialist → `admin.dashboard`). Dual-role behavior is therefore **not the same** on `/` vs post-login.

## Header channel / locale switch

Shared header posts to `admin.channels.switch` and `admin.locale.switch` (`role:Admin`). Specialists can see the dropdowns; switching is not authorized for this role. Channel used by the desk is `users.current_channel_id` / `channel_user` (and fallbacks in `ResolvesSalesChannel`). See [`../channels-locales/architecture.md`](../channels-locales/architecture.md).
