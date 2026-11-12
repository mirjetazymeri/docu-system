# Permissions, Roles, Access Levels

Back to [README](README.md). Flow: [architecture.md](architecture.md).

## How a permission string is checked

Admin controllers:

```text
$this->middleware('check.permission:user_view')->only(['index']);
```

`AutoAuthorizePermission` (`app/Http/Middleware/AutoAuthorizePermission.php`):

1. No user → redirect `login`
2. `isSuperAdmin()` → allow
3. `hasAccessLevelPermission($permission)` → allow
4. Else `abort(403, "Access denied: missing permission [{name}] via access level.")`

Blade `@can('user_view')` / `$user->can('user_view')` go through `Gate::before` (`app/Providers/AuthServiceProvider.php`) then Spatie if before returns `null`.

There is **no** `App\Policies\*` for User, Role, AccessLevel, etc. The only policy in the app is `ProductReviewPolicy` (`app/Policies/ProductReviewPolicy.php`), which uses `$user->can('review_edit')` / `review_delete` plus ownership.

---

## Spatie roles (area gates)

Seeded in `database/seeders/RoleSeeder.php` (`guard_name` `web`):

| Role name | Assigned on seed (by user id) | Route / redirect usage |
| --------- | ------------------------------ | ---------------------- |
| `Admin` | user id 1 | `role:Admin` on `/admin/*`; `GET /` → admin dashboard; Horizon `viewHorizon`; verification/reset mail branding |
| `User` | user id 2 | **None** in middleware or Fortify redirects |
| `Customer` | user id 3 | `role:Customer`; storefront register; sales-specialist customer search/create; login → customer dashboard |
| `Sales Specialist` | user id 4 | `role:Sales Specialist` on `/sales-specialist/*` |

Role names are **string literals** in `routes/web.php`, `FortifyServiceProvider`, sidebar, `AuthController`, `SalesSpecialist\CustomerController`, notifications.

Roles are **not** given Spatie permissions in this codebase.

---

## Access level: Super Admin

Seeded in `database/seeders/SuperAdminSeeder.php`:

| Field | Value |
| ----- | ----- |
| `name` | `Super Admin` |
| `description` | `Full access — every permission` |
| Permissions | `sync` of **all** rows in `permissions` at seed time |
| Attached to | User id 1, or email `admin@admin.com` / `admin@admin` |

Bypass is the **name** `Super Admin`, not the fact that all permissions are attached. A custom AL with every permission is still not Super Admin unless named that.

After seed, new permissions created in admin UI are **not** automatically added to Super Admin unless someone edits the AL or re-runs the seeder.

---

## Seeded modules and permissions

### Access Control (SuperAdminSeeder only)

| Permission | Protects |
| ---------- | -------- |
| `access_level_view` | Access level index |
| `access_level_create` | create, store |
| `access_level_edit` | edit, update |
| `access_level_delete` | destroy |
| `permission_view` | Permission index |
| `permission_create` | create, store |
| `permission_edit` | edit, update |
| `permission_delete` | destroy |
| `module_view` | Module index |
| `module_create` | create, store |
| `module_edit` | edit, update |
| `module_delete` | destroy |
| `role_view` | Role index |
| `role_create` | create |
| `role_edit` | edit |
| `role_delete` | delete |

### ModulePermissionSeeder

Format: `{resource}_{action}`. Module display name is `modules.name`.

| Module | Permissions |
| ------ | ----------- |
| Users | `user_view`, `user_create`, `user_edit`, `user_delete` |
| Posts | `post_view`, `post_create`, `post_edit`, `post_delete` — **no Posts feature in `app/`** |
| Dashboard | `dashboard_view` |
| Products | `product_view`, `product_create`, `product_edit`, `product_delete` |
| Categories | `category_view`, `category_create`, `category_edit`, `category_delete` |
| Attributes | `attribute_view`, `attribute_create`, `attribute_edit`, `attribute_delete` |
| Product Synonyms | `product_synonym_view`, `product_synonym_create`, `product_synonym_edit`, `product_synonym_delete` |
| Reviews | `review_view`, `review_edit`, `review_delete` |
| Catalog Rules | `catalog_rule_view`, `catalog_rule_create`, `catalog_rule_edit`, `catalog_rule_delete` |
| Promotions | `promotion_view`, `promotion_create`, `promotion_edit`, `promotion_delete` |
| Coupons | `coupon_view`, `coupon_create`, `coupon_edit`, `coupon_delete` |
| Carts | `cart_view`, `cart_edit` |
| Storefront Events | `storefront_event_view`, `storefront_event_delete` |
| Orders | `order_view`, `order_edit` |
| Assignments | `assignment_view`, `assignment_create`, `assignment_edit` |
| Addresses | `address_view`, `address_create`, `address_edit`, `address_delete` |
| User Groups | `user_group_view`, `user_group_create`, `user_group_edit`, `user_group_delete` |
| Employees | `employee_view`, `employee_create`, `employee_edit`, `employee_delete` |
| Newsletters | `newsletter_view`, `newsletter_create`, `newsletter_edit`, `newsletter_delete` |
| Theme | `theme_view`, `theme_edit` |
| Channels | `channel_view`, `channel_create`, `channel_edit`, `channel_delete` |
| Countries | `country_view`, `country_create`, `country_edit`, `country_delete` |
| Locales | `locale_view`, `locale_create`, `locale_edit`, `locale_delete` |
| Warehouses | `warehouse_view`, `warehouse_create`, `warehouse_edit`, `warehouse_delete` |
| Pages | `page_view`, `page_create`, `page_edit`, `page_delete` |
| Widgets | `widget_view`, `widget_create`, `widget_edit`, `widget_delete` |
| Media | `media_view`, `media_create`, `media_edit`, `media_delete` |
| Payment Methods | `payment_method_view`, `payment_method_create`, `payment_method_edit`, `payment_method_delete` |
| Shipping Methods | `shipping_method_view`, `shipping_method_create`, `shipping_method_edit`, `shipping_method_delete` |
| Tax Classes | `tax_class_view`, `tax_class_create`, `tax_class_edit`, `tax_class_delete` |
| Tax Rates | `tax_rate_view`, `tax_rate_create`, `tax_rate_edit`, `tax_rate_delete` |
| Settings | `setting_view`, `setting_edit` |
| System Cache | `system_cache_view`, `system_cache_edit` |
| Translations | `translation_view`, `translation_create`, `translation_edit`, `translation_delete` |
| Lookups | `lookup_view` |

`firstOrCreate` by permission **name** only; existing rows are not retargeted to a module.

Removed later: `external_catalog_view` / `external_catalog_edit` (`database/migrations/2026_08_24_191200_remove_external_catalog_permissions.php`).

---

## This module’s controller map

| Permission | Controller |
| ---------- | ---------- |
| `user_*` | `UserController` |
| `role_*` | `RoleController` |
| `permission_*` | `PermissionController` |
| `module_*` | `ModuleController` |
| `access_level_*` | `AccessLevelController` |
| `user_group_*` | `UserGroupController` |
| `employee_*` | `EmployeeController` |
| `address_*` | `UserAddressController` |

Storefront `/api/v1/me/addresses*` uses Sanctum only.

---

## Sidebar vs permissions

`resources/views/layouts/components/main-sidebar.blade.php`:

- Users menu: `@canany(['user_view', 'user_create', 'user_group_view', 'address_view'])`
- Access Control submenu: `@canany(['role_view', 'module_view', 'permission_view', 'access_level_view'])`
- **No** employees item (still routed)

`@can` follows `Gate::before` (Super Admin / access levels) then Spatie.

---

## Config

`config/permission.php`:

- Models: Spatie `Permission` and `Role` (not `App\Models\Permission`)
- `register_permission_check_method` => `true`
- `teams` => `false`
- `events_enabled` => `false`
- Cache key `spatie.permission.cache`, 24h
