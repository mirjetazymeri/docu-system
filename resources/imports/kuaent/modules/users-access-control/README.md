# Users & Access Control

Admin identity, Spatie roles, custom access levels, permission catalog (modules + permissions), employees, customer groups, and saved user addresses.

This is rest-commerce (`TestDocu-admin`): Laravel 12 / PHP 8.2.

## Purpose

Own the people and authorization records used by the admin panel, sales-specialist UI, and storefront API:

- `users` accounts (admin staff, sales specialists, customers)
- Spatie roles used as **area gates** (`Admin`, `Sales Specialist`, `Customer`, plus seeded `User`)
- Custom **access levels** that actually grant named permissions
- Permission catalog grouped by `modules`
- HR-style `employees` (not linked to `users`)
- `user_groups` used for pricing / catalog / coupons
- `user_addresses` (admin CRUD + authenticated storefront API)

## Responsibilities

- CRUD for users, roles, modules, permissions, access levels, user groups, employees, and addresses
- Assign roles, access levels, channels, and groups on a user
- Enforce admin route permissions via `check.permission` (access levels + Super Admin bypass)
- Enforce panel/area membership via `role` middleware (`Admin` / `Customer` / `Sales Specialist`)
- Keep at most one default shipping and one default billing address per user (admin service; API has a similar but not identical path)
- Seed the permission catalog, Super Admin access level, default roles, and starter user groups

## Source Code Locations

```text
app/Http/Controllers/Admin/UserController.php
app/Http/Controllers/Admin/RoleController.php
app/Http/Controllers/Admin/PermissionController.php
app/Http/Controllers/Admin/ModuleController.php
app/Http/Controllers/Admin/AccessLevelController.php
app/Http/Controllers/Admin/EmployeeController.php
app/Http/Controllers/Admin/UserGroupController.php
app/Http/Controllers/Admin/UserAddressController.php
app/Http/Controllers/Api/V1/AddressController.php
app/Services/UserAddressService.php
app/Models/User.php
app/Models/Employee.php
app/Models/Module.php
app/Models/Permission.php
app/Models/AccessLevel.php
app/Models/UserGroup.php
app/Models/UserAddress.php
app/Observers/UserObserver.php
app/Providers/AuthServiceProvider.php
app/Http/Middleware/AutoAuthorizePermission.php
app/Http/Middleware/RoleMiddleware.php
app/Http/Requests/Admin/UserAddress/StoreUserAddressRequest.php
app/Http/Requests/Admin/UserAddress/UpdateUserAddressRequest.php
bootstrap/app.php
config/permission.php
database/seeders/DatabaseSeeder.php
database/seeders/RoleSeeder.php
database/seeders/ModulePermissionSeeder.php
database/seeders/SuperAdminSeeder.php
database/seeders/UserGroupSeeder.php
database/factories/UserFactory.php
resources/views/admin/users/
resources/views/admin/roles/
resources/views/admin/permissions/
resources/views/admin/modules/
resources/views/admin/access_levels/
resources/views/admin/employees/
resources/views/admin/user_groups/
resources/views/admin/addresses/
routes/web.php
routes/api.php
```

Related code this module **uses** but does not own: Fortify/Sanctum auth, `App\Models\Channel`, `App\Models\Country`, `App\Support\CheckoutAddressFields`, `App\Support\AuthSettings`, `App\Services\Mail\MailService`. See [dependencies.md](dependencies.md).

## Documentation Index

| File | Contents |
| ---- | -------- |
| [architecture.md](architecture.md) | Dual authorization model, request flow, observed gaps |
| [models.md](models.md) | Eloquent models, relationships, business methods |
| [database.md](database.md) | Tables, migrations, seeders, factory |
| [controllers.md](controllers.md) | Admin + storefront address controllers |
| [services.md](services.md) | `UserAddressService` |
| [requests-validation.md](requests-validation.md) | FormRequests and inline validation |
| [routes.md](routes.md) | Admin web routes |
| [api.md](api.md) | `GET/POST/PUT/DELETE /api/v1/me/addresses*` |
| [permissions.md](permissions.md) | Seeded modules, permissions, roles, access levels |
| [workflows.md](workflows.md) | Seed bootstrap, admin user CRUD, address defaults, customer creation |
| [dependencies.md](dependencies.md) | Incoming / outgoing module deps |
| [tests.md](tests.md) | What tests exist (almost none for this module) |

## Main Components

| Type | Class | Responsibility |
| ---- | ----- | -------------- |
| Model | `App\Models\User` | Authenticatable account; Spatie `HasRoles`; access levels, groups, addresses, channels |
| Model | `App\Models\AccessLevel` | Named permission bundle assigned to users |
| Model | `App\Models\Permission` | App catalog row on `permissions` (`module_id`); not Spatie’s Permission class |
| Model | `App\Models\Module` | Display grouping for permissions |
| Model | `App\Models\UserGroup` | Customer/pricing group with slug + membership |
| Model | `App\Models\UserAddress` | Saved shipping/billing address |
| Model | `App\Models\Employee` | Standalone HR record (no `user_id`) |
| Spatie model | `Spatie\Permission\Models\Role` | Role name + `model_has_roles` assignments |
| Controller | `App\Http\Controllers\Admin\UserController` | Admin user CRUD + role/AL/channel/group sync |
| Controller | `App\Http\Controllers\Admin\RoleController` | Role name CRUD |
| Controller | `App\Http\Controllers\Admin\PermissionController` | Permission catalog CRUD |
| Controller | `App\Http\Controllers\Admin\ModuleController` | Module catalog CRUD |
| Controller | `App\Http\Controllers\Admin\AccessLevelController` | Access level + permission attach |
| Controller | `App\Http\Controllers\Admin\UserGroupController` | Group CRUD + membership |
| Controller | `App\Http\Controllers\Admin\EmployeeController` | Employee CRUD + avatar upload |
| Controller | `App\Http\Controllers\Admin\UserAddressController` | Admin address CRUD via service |
| Controller | `App\Http\Controllers\Api\V1\AddressController` | Storefront address book |
| Service | `App\Services\UserAddressService` | Default shipping/billing uniqueness |
| Middleware | `App\Http\Middleware\AutoAuthorizePermission` | Alias `check.permission` |
| Middleware | `App\Http\Middleware\RoleMiddleware` | Alias `role` |
| Provider | `App\Providers\AuthServiceProvider` | `Gate::before` Super Admin + access-level permissions |
| Observer | `App\Observers\UserObserver` | Welcome + admin-new-user mail after insert |
| FormRequest | `StoreUserAddressRequest` / `UpdateUserAddressRequest` | Admin address validation |
| Policy | — | **No policies** in this module. The only app policy is `ProductReviewPolicy` (reviews module). |

## Module Dependencies

This module depends on:

- [auth](../auth/README.md) — Fortify session login, `verified.if-required`, Sanctum tokens, password hashing, 2FA columns, `AuthSettings`
- [channels-locales](../channels-locales/README.md) — `Channel` assignment on users, `current_channel_id`, `User.locale`, `SetUserLocale`, `SetCurrentChannel`
- [geo-countries](../geo-countries/README.md) — `countries.code` for address `country_code`
- [mail](../../shared/README.md#mail) — `UserObserver` → `MailService::sendWelcome` / `notifyAdminNewUser`
- [settings-system](../settings-system/README.md) (API addresses only) — `CheckoutAddressFields` toggles which storefront address fields are required/hidden

This module is used by:

- **sales-specialist** — `User::role('Customer')`, `SalesSpecialist\CustomerController` creates Customer users + `customer` group
- **orders** — `Order.user()`, `Order.salesSpecialist()`; sales-specialist order placement
- **catalog-rules-pricing / catalog-products / coupons** — `UserGroup` membership for prices, projections, coupon scoping
- **auth** — `AuthController::register` assigns `Customer` role + `customer` group
- **Horizon** — `viewHorizon` gate allows `hasRole('Admin')`
- Almost every admin controller — `check.permission:{resource}_{action}` middleware

See [dependencies.md](dependencies.md).

## Entry Points

| Kind | Entry |
| ---- | ----- |
| Web (admin) | `routes/web.php` group `prefix('admin')` + `role:Admin` |
| Web (home routing) | `GET /` redirects by Spatie role |
| API | `routes/api.php` `auth:sanctum` + `verified.if-required` → `/api/v1/me/addresses*` |
| Seeders | `DatabaseSeeder` → `UserGroupSeeder`, `ModulePermissionSeeder`, `SuperAdminSeeder`, `RoleSeeder` |
| Observer | `UserObserver::created` on every `User` insert |
| Sidebar | `resources/views/layouts/components/main-sidebar.blade.php` (Users + Access Control; employees have **no** sidebar link) |

No artisan commands, jobs, or scheduled tasks belong to this module.

## Important Business Rules

Extracted from code (not inferred product intent):

- Admin URLs under `/admin/*` require Spatie role **`Admin`**. Named permissions are a **second** check via access levels (or the Super Admin bypass).
- Super Admin is an **access level name**, not a Spatie role. `User::isSuperAdmin()` is `accessLevels` contains name `Super Admin`.
- `check.permission` does **not** consult Spatie `role_has_permissions` / `model_has_permissions`. Missing access-level permission → 403.
- `Gate::before` grants Super Admin **or** access-level permission, then Spatie may still run. Application code never calls `givePermissionTo` / `syncPermissions`.
- Seeded Spatie roles: `Admin`, `User`, `Customer`, `Sales Specialist`. Role `User` is not referenced by route middleware or login redirects.
- `ModulePermissionSeeder` creates a **`Posts`** module and `post_*` permissions. There is no Posts controller, model, route, or view in this repository.
- Access Control permissions (`role_*`, `module_*`, `permission_*`, `access_level_*`) are seeded in `SuperAdminSeeder`, not in `ModulePermissionSeeder`.
- Storefront `POST /api/v1/auth/register` and sales-specialist customer create assign role `Customer` and group slug `customer` (`UserGroup::firstOrCreate`). That group is **not** in `UserGroupSeeder` (which seeds General, Wholesale, VIP, Guest).
- `Employee` is not a `User`. No `employees.user_id`.
- Employee form/validation allows status `terminated`; the migration enum is `active`, `inactive`, `on_leave`.
- Admin address service enforces one default shipping and one default billing per user; if none exist after write/delete, the latest remaining address is promoted.
- API address `destroy` soft-deletes only; it does **not** promote a new default.
- Admin address picker loads at most 200 users (`User::orderBy('name')->limit(200)`).
- `UserObserver` sends welcome + admin-new-user mail on every user insert (including seeders and admin CRUD), deferred with `DB::afterCommit`.

## Authorization Snapshot

```text
Request → auth → verified.if-required → set.user.locale
    → role:{Admin|Customer|Sales Specialist}   (Spatie HasRoles)
    → controller check.permission:{name}     (access levels only + Super Admin)
```

Details: [architecture.md](architecture.md), [permissions.md](permissions.md).
