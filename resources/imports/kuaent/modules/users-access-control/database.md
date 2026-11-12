# Database

Back to [README](README.md). Column types come from migrations, not model `$casts`.

## Tables Owned or Primarily Used Here

| Table | Purpose |
| ----- | ------- |
| `users` | Accounts |
| `modules` | Permission groups |
| `permissions` | Named abilities (+ Spatie) |
| `roles` | Spatie role names |
| `model_has_roles` | User ↔ role |
| `model_has_permissions` | User ↔ permission (unused by app code) |
| `role_has_permissions` | Role ↔ permission (unused by app code) |
| `access_levels` | Permission bundles |
| `access_level_permission` | AL ↔ permission |
| `user_access_level` | User ↔ AL |
| `user_groups` | Groups |
| `user_group_user` | Membership |
| `employees` | HR records |
| `user_addresses` | Address book |
| `channel_user` | User ↔ channel (schema lives with channels; assigned from User admin) |

`password_reset_tokens`, `sessions`, `personal_access_tokens` are auth/session/Sanctum infrastructure on or next to `users`. Not documented in depth here.

---

## Table: `users`

### Purpose

Login identity.

### Created By

- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/2025_08_06_134009_add_two_factor_columns_to_users_table.php`
- `database/migrations/2025_08_09_161533_add_locale_to_users_table.php`
- `database/migrations/2025_08_09_195634_add_preferences_to_users.php`
- `database/migrations/2026_03_17_160601_add_current_channel_id_to_users_table.php`

### Columns

| Column | Type | Nullable | Default | Description |
| ------ | ---- | -------- | ------- | ----------- |
| `id` | bigint | no | auto | PK |
| `current_channel_id` | FK | yes | null | `constrained('channels')->nullOnDelete()` |
| `name` | string | no | | |
| `email` | string | no | | unique |
| `email_verified_at` | timestamp | yes | null | |
| `password` | string | no | | |
| `two_factor_secret` | text | yes | null | |
| `two_factor_recovery_codes` | text | yes | null | |
| `two_factor_confirmed_at` | timestamp | yes | null | |
| `remember_token` | string | yes | null | |
| `locale` | string(5) | no | `en` | |
| `preferences` | json | yes | null | |
| `created_at` / `updated_at` | timestamps | yes | | |

`down()` on `add_current_channel_id_to_users_table.php` is empty (does not drop the column).

Same migration file also creates `password_reset_tokens` and `sessions`.

---

## Table: `modules`

Source: `database/migrations/2025_08_06_122133_create_modules_table.php`

| Column | Type | Nullable | Default | Description |
| ------ | ---- | -------- | ------- | ----------- |
| `id` | bigint | no | auto | PK |
| `name` | string | no | | unique |
| `description` | string | yes | null | |
| timestamps | | yes | | |

---

## Table: `permissions` / `roles` / Spatie pivots

Source: `database/migrations/2025_08_06_134041_create_permission_tables.php`

Uses `config('permission.table_names')`. Teams feature is off (`config/permission.php` `'teams' => false`), so team columns are not created unless `permission.testing` is true.

### `permissions`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | bigIncrements | |
| `name` | string | |
| `guard_name` | string | |
| `module_id` | FK `modules.id` cascade | **Custom** vs stock Spatie migration |
| timestamps | | |
| unique | `(name, guard_name)` | |

### `roles`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | bigIncrements | |
| `name` | string | |
| `guard_name` | string | |
| timestamps | | |
| unique | `(name, guard_name)` | |

### `model_has_permissions`

`permission_id` + `model_type` + `model_id` (config `model_morph_key`). FK cascade to permissions.

### `model_has_roles`

`role_id` + `model_type` + `model_id`. FK cascade to roles.

### `role_has_permissions`

`(permission_id, role_id)` PK. Cascades to both.

Cache forget on migrate: `config('permission.cache.key')`.

---

## Table: `access_levels`

Source: `database/migrations/2025_08_24_212137_create_access_levels_table.php`

| Column | Type | Nullable | Default |
| ------ | ---- | -------- | ------- |
| `id` | bigint | no | auto |
| `name` | string | no | unique |
| `description` | string | yes | null |
| timestamps | | | |

---

## Table: `access_level_permission`

Source: `database/migrations/2025_08_24_212152_create_access_level_permission_table.php`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | bigint | PK |
| `access_level_id` | FK cascade | |
| `permission_id` | FK `permissions` cascade | |
| timestamps | | |
| unique | `(access_level_id, permission_id)` | |

---

## Table: `user_access_level`

Source: `database/migrations/2025_08_24_213613_create_user_access_level_table.php`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | bigint | PK |
| `user_id` | FK users cascade | |
| `access_level_id` | FK access_levels cascade | |
| timestamps | | |
| unique | `(user_id, access_level_id)` | |

---

## Table: `user_groups`

Source: `database/migrations/2025_08_25_013847_create_user_groups_table.php`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | bigint | PK |
| `name` | string unique | |
| `slug` | string unique | |
| `description` | text | nullable |
| `is_active` | boolean | default true |
| `deleted_at` | softDeletes | |
| timestamps | | |

---

## Table: `user_group_user`

Same migration.

| Column | Type | Notes |
| ------ | ---- | ----- |
| `user_group_id` | FK user_groups | cascade update/delete |
| `user_id` | FK users | cascade update/delete |
| PK | `(user_group_id, user_id)` | no timestamps |

---

## Table: `employees`

Source: `database/migrations/2025_08_09_223537_create_employees_table.php`

| Column | Type | Nullable | Default |
| ------ | ---- | -------- | ------- |
| `id` | bigint | no | auto |
| `first_name` | string | no | |
| `last_name` | string | no | |
| `email` | string | no | unique |
| `phone` | string | yes | null |
| `position` | string | yes | null |
| `department` | string | yes | null |
| `hired_at` | date | yes | null |
| `salary` | decimal(12,2) | yes | null |
| `status` | enum(`active`,`inactive`,`on_leave`) | no | `active` |
| `avatar_path` | string | yes | null |
| timestamps | | | |
| `deleted_at` | softDeletes | | |

Indexes: `(last_name, first_name)`, `status`.

---

## Table: `user_addresses`

Source: `database/migrations/2025_08_27_235541_create_user_addresses_table.php.php`  
NUI: `database/migrations/2026_04_02_120000_add_nui_to_user_addresses_table.php`

| Column | Type | Nullable | Default |
| ------ | ---- | -------- | ------- |
| `id` | bigint | no | auto |
| `user_id` | FK users cascade | no | |
| `label` | string(100) | yes | |
| `first_name` | string(100) | no | |
| `last_name` | string(100) | no | |
| `company` | string(150) | yes | |
| `nui` | string(100) | yes | |
| `phone` | string(50) | yes | |
| `address_line1` | string(191) | no | |
| `address_line2` | string(191) | yes | |
| `city` | string(120) | no | |
| `state_code` | string(60) | yes | |
| `postcode` | string(40) | yes | |
| `country_code` | string(2) | no | |
| `is_default_shipping` | boolean | no | false |
| `is_default_billing` | boolean | no | false |
| `default_shipping_user` | unsignedBigInteger virtual | | `CASE WHEN is_default_shipping THEN user_id ELSE NULL END` |
| `default_billing_user` | unsignedBigInteger virtual | | analog |
| `meta` | json | yes | |
| timestamps | | | |
| `deleted_at` | softDeletes | | |

Index: `user_id`. Unique: `default_shipping_user`, `default_billing_user`.

Filename contains a double `.php.php` suffix.

---

## Table: `channel_user`

Source: `database/migrations/2026_03_17_153753_create_channel_user_table.php`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | bigint | PK |
| `user_id` | FK users cascade | |
| `channel_id` | FK channels cascade | |
| timestamps | | |

Owned jointly with channels-locales; written from `UserController`.

---

## Data migration: remove External Catalog permissions

`database/migrations/2026_08_24_191200_remove_external_catalog_permissions.php`

Deletes permissions `external_catalog_view` / `external_catalog_edit`, their rows in `access_level_permission`, `role_has_permissions`, `model_has_permissions`, module `External Catalog`, and settings keys `product.external_import.%`. Not re-seeded by current `ModulePermissionSeeder`.

---

## Seeders

Order in `database/seeders/DatabaseSeeder.php`:

1. `User::firstOrCreate` for four emails (`admin@admin.com`, `user@user.com`, `customer@customer.com`, `sales@sales.com`) — passwords are in the seeder source; not repeated here
2. `UserGroupSeeder`
3. Channel / country / shipping / city / warehouse / locale seeders (other modules)
4. `ModulePermissionSeeder`
5. `SuperAdminSeeder`
6. `RoleSeeder`

### `UserGroupSeeder`

`updateOrCreate` by slug:

| Name | Slug | Description (seeded) | Active |
| ---- | ---- | --------------------- | ------ |
| General | `general` | Default customer group | yes |
| Wholesale | `wholesale` | Wholesale customers with special pricing | yes |
| VIP | `vip` | VIP customers with exclusive offers | yes |
| Guest | `guest` | Guest users (not registered) | yes |

Runtime `firstOrCreate(['slug' => 'customer'], ['name' => 'Customer'])` in `AuthController` and `SalesSpecialist\CustomerController` can add a fifth group.

### `ModulePermissionSeeder`

Idempotent `Module::firstOrCreate` + `Permission::firstOrCreate` by `name`. Does **not** seed Access Control permissions (comment in seeder). Full list: [permissions.md](permissions.md). Includes unused **Posts**.

### `SuperAdminSeeder`

1. Module `Access Control`
2. Permissions `access_level_*`, `permission_*`, `module_*`, `role_*` (view/create/edit/delete)
3. Access level `Super Admin`
4. `sync` **all** `permissions.id` onto that AL
5. Attach AL to `User::find(1)` or email `admin@admin.com` / `admin@admin`

### `RoleSeeder`

`Role::firstOrCreate` `Admin`, `User`, `Customer`, `Sales Specialist` (`guard_name` `web`).

Then `assignRole` by **user id**: 1 Admin, 2 User, 3 Customer, 4 Sales Specialist. Warns if the id is missing.

### Employees / addresses

No employee or address seeders.

---

## Factory

`database/factories/UserFactory.php` only. No factories for Employee, UserGroup, UserAddress, AccessLevel, Module, or `App\Models\Permission`.
