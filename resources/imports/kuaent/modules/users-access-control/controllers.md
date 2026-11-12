# Controllers

Back to [README](README.md). Routes: [routes.md](routes.md), [api.md](api.md).

Unless noted, admin controllers live under `App\Http\Controllers\Admin` and sit behind `auth`, `verified.if-required`, `set.user.locale`, `role:Admin`.

Index actions: if `$request->ajax()`, return DataTables JSON; otherwise return a Blade view.

No events or jobs are dispatched from these controllers.

---

## `UserController`

Source: `app/Http/Controllers/Admin/UserController.php`

### Responsibility

Admin user list/create/edit/delete. Syncs Spatie roles, access levels, channels, user groups.

### Dependencies

`User`, `AccessLevel`, `UserGroup`, `Spatie\Permission\Models\Role`, `Channel`, `Hash`, `DB`, DataTables.

### Middleware

| Permission | Methods |
| ---------- | ------- |
| `user_view` | `index` |
| `user_create` | `create` (GET + POST) |
| `user_edit` | `edit` (GET + POST) |
| `user_delete` | `delete` |

### `index(Request)`

- Query: `User::with(['roles', 'groups'])`
- Optional filters: `role_id`, `group_id`
- DataTables columns: roles (badges), groups (links to `admin.user_groups.edit`), `admin.users.actions`
- Non-ajax: all roles, all groups, `model_has_roles` counts, `user_group_user` counts, `User::count()`
- View: `admin.users.index`

### `create(Request)`

- GET: `Channel::all()`, `Role::all()`, `AccessLevel::all()`, **active** groups only
- POST validates name/email/password/roles/access_levels/groups (see [requests-validation.md](requests-validation.md))
- Creates user with `email_verified_at = now()`, `Hash::make` password
- `syncRoles` if `roles` filled; `accessLevels()->sync` if filled; `channels()->sync` if filled; `groups()->sync` if filled
- Redirect `admin.users.index` success `User created.`
- View: `admin.users.create`

`channels` is not in the validate array; still synced when present.

### `edit(Request, $id)`

- Loads user `with(['roles', 'accessLevels', 'channels', 'groups'])`
- POST: unique email ignore current id; password optional
- Always `syncRoles` from `roles` input (empty → no roles)
- Access levels: sync or `detach()` if empty
- Channels: sync or `detach()` if empty
- Groups: `sync($request->input('groups', []))`
- Edit form lists **all** groups (including inactive)
- View: `admin.users.edit`

### `delete($id)`

Hard `User::delete()`. Cascades addresses (FK), access-level pivot, channel pivot, group pivot, Spatie `model_has_roles`. Redirect success `User deleted.`

Triggers `UserObserver` only on create, not delete.

---

## `RoleController`

Source: `app/Http/Controllers/Admin/RoleController.php`

### Responsibility

Spatie role **name** CRUD. Does not attach permissions.

### Middleware

`role_view` / `role_create` / `role_edit` / `role_delete` on `index` / `create` / `edit` / `delete`.

### Methods

| Method | Behavior |
| ------ | -------- |
| `index` | DataTables on `Role::query()`; view `admin.roles.index` |
| `create` | POST: `name` required unique `roles`; `Role::create(['name' => …])`; view `admin.roles.create` |
| `edit($id)` | POST: unique name ignore id; `update` name; view `admin.roles.edit` |
| `delete($id)` | `Role::delete()` (Spatie cascade on `model_has_roles`) |

`guard_name` is not set in `create()`; Spatie typically defaults to `web` via package config. `RoleSeeder` sets `guard_name` explicitly.

### Missing method

`routes/web.php` names `admin.roles.attach.permissions` → `RoleController::attachPermissions`. **Not implemented.** Invoking that route would error.

---

## `PermissionController`

Source: `app/Http/Controllers/Admin/PermissionController.php`

Laravel `Route::resource('permissions')`. Middleware also lists `store` / `update` / `destroy`. **No `show` method** (resource still registers `GET permissions/{permission}`).

| Method | Behavior |
| ------ | -------- |
| `index` | DataTables with `module`; filter/order `module_name` via join |
| `create` / `edit` | `Module::orderBy('name')`; views `admin.permissions.create` / `edit` |
| `store` / `update` | `name` unique, `module_id` exists; `guard_name` forced `web` |
| `destroy` | `Permission::delete()` (cascades pivots) |

---

## `ModuleController`

Source: `app/Http/Controllers/Admin/ModuleController.php`

Resource. No `show()`.

| Method | Behavior |
| ------ | -------- |
| `index` | DataTables; empty description shown as `—` |
| `store` / `update` | `name` unique; `only('name','description')` |
| `destroy` | `Module::delete()` → cascade permissions |

---

## `AccessLevelController`

Source: `app/Http/Controllers/Admin/AccessLevelController.php`

Resource. No `show()`.

| Method | Behavior |
| ------ | -------- |
| `index` | DataTables |
| `create` / `edit` | `Permission::with('module')->orderBy('name')`; permissions UI grouped by module |
| `store` | Create name/description; `permissions()->attach` if `has('permissions')` |
| `update` | `permissions()->sync($request->permissions ?? [])` |
| `destroy` | `AccessLevel::delete()` (cascade user + permission pivots) |

Deleting Super Admin would remove the bypass for users who only had that AL.

---

## `UserGroupController`

Source: `app/Http/Controllers/Admin/UserGroupController.php`

Custom routes (GET/POST create/edit, DELETE delete). Middleware: `user_group_*` on `index` / `create` / `edit` / `delete`.

| Method | Behavior |
| ------ | -------- |
| `index` | `withCount('users')`, `with(['users:id,name,email'])`; member preview first 3 names |
| `create` POST | Unique name/slug; `is_active` defaults true if omitted; `users()->sync` if provided |
| `edit` POST | Unique ignore self; `is_active` false if checkbox omitted; always `sync` users |
| `delete` | Soft delete |

GET create/edit: `User::orderBy('name')->get()` (no 200 limit).

---

## `EmployeeController`

Source: `app/Http/Controllers/Admin/EmployeeController.php`

Laravel resource **including** `show`. Uses `DataTables` import (not the Facades alias).

### Middleware

`employee_view` → index, show; create → create, store; edit → edit, update; delete → destroy.

### Avatar

Valid upload → `storeAs('uploads/avatars', '{time}_{first}_{last}.{ext}', 'public')`. Update/destroy deletes previous file via `Storage::disk('public')`.

### Status

`validatedData()`: `status` `in:active,inactive,terminated`. DB enum does not include `terminated` (see [database.md](database.md)).

Redirect after store/update: `admin.employees.show`. After destroy: `admin.employees.index`.

---

## `UserAddressController`

Source: `app/Http/Controllers/Admin/UserAddressController.php`

Injects `UserAddressService`. FormRequests for store/update.

| Permission | Methods |
| ---------- | ------- |
| `address_view` | `index` |
| `address_create` | `create`, `store` |
| `address_edit` | `edit`, `update` |
| `address_delete` | `delete` |

### `index`

Optional `user_id`, `q` (label, names, company, line1, city, postcode). Country names via `Country::pluck('name','code')`. Users for filter dropdown: `limit(200)`. View `admin.addresses.index`.

### `create` / `edit`

Users `limit(200)`; countries `pluck('name','code')`. Views `admin.addresses.create` / `edit`.

### `store` / `update` / `delete`

Delegate to `UserAddressService`. Store redirects to edit; update `back()`; delete to index.

---

## `App\Http\Controllers\Api\V1\AddressController`

Source: `app/Http/Controllers/Api/V1/AddressController.php`

Storefront address book for the Sanctum user. Does **not** use `UserAddressService`. Uses `CheckoutAddressFields` for per-field show/required/prohibited. Resolves `Country` names for presentation.

Middleware: `channel.api`, `locale.api`, `auth:sanctum`, `verified.if-required`. No `check.permission`.

| Method | Behavior |
| ------ | -------- |
| `index` | `$req->user()->addresses()->latest('id')` → `{ data: [...] }` |
| `store` | Merge grouped payload; validate; transaction create + default flags; **201** |
| `update` | `findOrFail` on **current user’s** addresses; 404 if other user’s id |
| `destroy` | Soft delete; **204** empty body; no default promotion |
| `setDefault` | Body `type` ∈ `shipping`,`billing`,`both`; clears then sets flags |

Payload shapes: [api.md](api.md).

---

## Controllers outside this folder that write users/groups

Owned by other modules; listed so ownership is clear:

| Class | What it does with this module’s data |
| ----- | ----------------------------------- |
| `App\Http\Controllers\Api\V1\AuthController` | Creates `User`, `assignRole('Customer')`, `groups()->syncWithoutDetaching` slug `customer` |
| `App\Http\Controllers\SalesSpecialist\CustomerController` | `User::role('Customer')` search; create user + Customer role + `customer` group |
| `App\Actions\Fortify\CreateNewUser` | Creates `User` **without** role or access level (auth) |

---

## Views

| Area | Path |
| ---- | ---- |
| Users | `resources/views/admin/users/{index,create,edit,actions}.blade.php` |
| Roles | `resources/views/admin/roles/{index,create,edit,actions}.blade.php` |
| Permissions | `resources/views/admin/permissions/{index,create,edit,actions}.blade.php` |
| Modules | `resources/views/admin/modules/{index,create,edit,actions}.blade.php` |
| Access levels | `resources/views/admin/access_levels/{index,create,edit,actions,_permissions_cards}.blade.php` |
| User groups | `resources/views/admin/user_groups/{index,create,edit,actions,_members_field,_members_scripts}.blade.php` |
| Employees | `resources/views/admin/employees/{index,create,edit,show,actions,_form}.blade.php` |
| Addresses | `resources/views/admin/addresses/{index,create,edit,actions,_form}.blade.php` |
| Nav | `resources/views/layouts/components/main-sidebar.blade.php` |
