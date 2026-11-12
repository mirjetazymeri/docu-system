# Routes (Admin Web)

Back to [README](README.md). Storefront addresses: [api.md](api.md).

All tables below are inside `routes/web.php`:

```text
middleware: auth, verified.if-required, set.user.locale, role:Admin
prefix: admin
name: admin.
```

`role:Admin` is `App\Http\Middleware\RoleMiddleware` (`bootstrap/app.php`). Permission column is the `check.permission:` argument on the controller constructor (`AutoAuthorizePermission`).

Laravel `Route::resource` also registers `show` for access levels, modules, and permissions; those controller methods **do not exist**.

---

## Access levels

`Route::resource('access_levels', AccessLevelController::class)`

| Method | URI | Name | Controller | Permission |
| ------ | --- | ---- | ---------- | ---------- |
| GET | `/admin/access_levels` | `admin.access_levels.index` | `index` | `access_level_view` |
| GET | `/admin/access_levels/create` | `admin.access_levels.create` | `create` | `access_level_create` |
| POST | `/admin/access_levels` | `admin.access_levels.store` | `store` | `access_level_create` |
| GET | `/admin/access_levels/{access_level}` | `admin.access_levels.show` | `show` | **no method** |
| GET | `/admin/access_levels/{access_level}/edit` | `admin.access_levels.edit` | `edit` | `access_level_edit` |
| PUT/PATCH | `/admin/access_levels/{access_level}` | `admin.access_levels.update` | `update` | `access_level_edit` |
| DELETE | `/admin/access_levels/{access_level}` | `admin.access_levels.destroy` | `destroy` | `access_level_delete` |

---

## Modules

`Route::resource('modules', ModuleController::class)`

| Method | URI | Name | Permission |
| ------ | --- | ---- | ---------- |
| GET | `/admin/modules` | `admin.modules.index` | `module_view` |
| GET | `/admin/modules/create` | `admin.modules.create` | `module_create` |
| POST | `/admin/modules` | `admin.modules.store` | `module_create` |
| GET | `/admin/modules/{module}` | `admin.modules.show` | **no method** |
| GET | `/admin/modules/{module}/edit` | `admin.modules.edit` | `module_edit` |
| PUT/PATCH | `/admin/modules/{module}` | `admin.modules.update` | `module_edit` |
| DELETE | `/admin/modules/{module}` | `admin.modules.destroy` | `module_delete` |

---

## Permissions

`Route::resource('permissions', PermissionController::class)`

| Method | URI | Name | Permission |
| ------ | --- | ---- | ---------- |
| GET | `/admin/permissions` | `admin.permissions.index` | `permission_view` |
| GET | `/admin/permissions/create` | `admin.permissions.create` | `permission_create` |
| POST | `/admin/permissions` | `admin.permissions.store` | `permission_create` |
| GET | `/admin/permissions/{permission}` | `admin.permissions.show` | **no method** |
| GET | `/admin/permissions/{permission}/edit` | `admin.permissions.edit` | `permission_edit` |
| PUT/PATCH | `/admin/permissions/{permission}` | `admin.permissions.update` | `permission_edit` |
| DELETE | `/admin/permissions/{permission}` | `admin.permissions.destroy` | `permission_delete` |

---

## Roles (custom)

| Method | URI | Name | Permission |
| ------ | --- | ---- | ---------- |
| GET | `/admin/roles` | `admin.roles.index` | `role_view` |
| GET/POST | `/admin/roles/create` | `admin.roles.create` | `role_create` |
| GET/POST | `/admin/roles/edit/{id}` | `admin.roles.edit` | `role_edit` |
| DELETE | `/admin/roles/delete/{id}` | `admin.roles.delete` | `role_delete` |
| POST | `/admin/roles/attach-permissions/{id}` | `admin.roles.attach.permissions` | none on constructor; **method missing** |

---

## Users (custom)

| Method | URI | Name | Permission |
| ------ | --- | ---- | ---------- |
| GET | `/admin/users` | `admin.users.index` | `user_view` |
| GET/POST | `/admin/users/create` | `admin.users.create` | `user_create` |
| GET/POST | `/admin/users/edit/{id}` | `admin.users.edit` | `user_edit` |
| DELETE | `/admin/users/delete/{id}` | `admin.users.delete` | `user_delete` |

---

## User groups (custom)

| Method | URI | Name | Permission |
| ------ | --- | ---- | ---------- |
| GET | `/admin/user-groups` | `admin.user_groups.index` | `user_group_view` |
| GET/POST | `/admin/user-groups/create` | `admin.user_groups.create` | `user_group_create` |
| GET/POST | `/admin/user-groups/edit/{id}` | `admin.user_groups.edit` | `user_group_edit` |
| DELETE | `/admin/user-groups/{id}` | `admin.user_groups.delete` | `user_group_delete` |

---

## Employees (resource)

`Route::resource('employees', EmployeeController::class)`

| Method | URI | Name | Permission |
| ------ | --- | ---- | ---------- |
| GET | `/admin/employees` | `admin.employees.index` | `employee_view` |
| GET | `/admin/employees/create` | `admin.employees.create` | `employee_create` |
| POST | `/admin/employees` | `admin.employees.store` | `employee_create` |
| GET | `/admin/employees/{employee}` | `admin.employees.show` | `employee_view` |
| GET | `/admin/employees/{employee}/edit` | `admin.employees.edit` | `employee_edit` |
| PUT/PATCH | `/admin/employees/{employee}` | `admin.employees.update` | `employee_edit` |
| DELETE | `/admin/employees/{employee}` | `admin.employees.destroy` | `employee_delete` |

---

## Addresses (custom)

| Method | URI | Name | Permission |
| ------ | --- | ---- | ---------- |
| GET | `/admin/addresses` | `admin.addresses.index` | `address_view` |
| GET | `/admin/addresses/create` | `admin.addresses.create` | `address_create` |
| POST | `/admin/addresses` | `admin.addresses.store` | `address_create` |
| GET | `/admin/addresses/edit/{id}` | `admin.addresses.edit` | `address_edit` |
| PUT | `/admin/addresses/{id}` | `admin.addresses.update` | `address_edit` |
| DELETE | `/admin/addresses/{id}` | `admin.addresses.delete` | `address_delete` |

---

## Related web routes (not this module’s CRUD)

| Method | URI | Name | How it uses this module |
| ------ | --- | ---- | ---------------------- |
| GET | `/` | (closure) | Redirect by `hasRole` |
| GET | `/customer/dashboard` | `customer.dashboard` | `role:Customer` |
| GET | `/sales-specialist/dashboard` | `sales-specialist.dashboard` | `role:Sales Specialist` |
| GET | `/sales-specialist/customers/search` | `sales-specialist.customers.search` | `User::role('Customer')` |
| POST | `/sales-specialist/customers` | `sales-specialist.customers.store` | Creates `User` + Customer role + group |
| PATCH | `/admin/orders/{order}/addresses` | `admin.orders.addresses.update` | **Order** addresses (`OrderAddress`), not `user_addresses` |

---

## DataTables

Index GET URLs are reused for XHR (`$request->ajax()`). Same permission as `index`.
