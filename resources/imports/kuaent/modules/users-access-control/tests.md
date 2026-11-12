# Tests

Back to [README](README.md).

Repository tests were searched under `tests/` for User, Role, Permission, AccessLevel, Employee, UserGroup, UserAddress, `me/addresses`, `hasAccessLevelPermission`, `AutoAuthorizePermission`, and admin user routes.

## Dedicated coverage for this module

**None found.** There is no feature or unit test that hits:

- `UserController`, `RoleController`, `PermissionController`, `ModuleController`, `AccessLevelController`, `EmployeeController`, `UserGroupController`, `UserAddressController`
- `UserAddressService`
- `AutoAuthorizePermission` / `RoleMiddleware` / `Gate::before`
- `Api\V1\AddressController`
- Seeders for roles / modules / Super Admin / user groups

## Tests that touch `User` as a fixture

| Test | Type | What it does with users |
| ---- | ---- | ----------------------- |
| `tests/Feature/Configurable/ConfigurableTestCase.php` | Feature base | `User::query()->create(...)` as `$this->admin`, `actingAs`; attaches a channel. Does not assign Spatie roles or access levels. |
| `tests/Feature/Bundle/BundleTestCase.php` | Feature base | Same pattern: create `User`, attach channel, `actingAs`. |
| Configurable / Bundle feature tests | Feature | Inherit the admin user above; catalog/cart behavior, not ACL. |

`UserFactory` exists (`database/factories/UserFactory.php`) but is not used by a test in this module’s area (no `User::factory()` under `tests/`).

## Related but not this module

| Test | Type | Covers |
| ---- | ---- | ------ |
| `tests/Unit/CheckoutAddressFieldsTest.php` | Unit | `CheckoutAddressFields` billing rules from config. Used by `AddressController` and checkout, **not** admin addresses. Does not create `UserAddress` rows. |
| `tests/Feature/ExampleTest.php` | Feature | `GET /` expects 200. Guest `/` actually redirects to login in `routes/web.php` — not an ACL test. |
| `tests/Unit/PersonNameTest.php` | Unit | Name splitting (sales-specialist billing prefill), not user ACL. |

## Important scenarios apparently not tested

After searching `tests/`, these behaviors have **no** matching tests:

- Super Admin bypass vs named access-level permission
- `role:Admin` vs missing access level (403 on `check.permission`)
- Role `User` cannot enter admin or customer groups
- Role CRUD / attach-permissions dead route
- User sync of roles, access levels, channels, groups
- User group slug generation and soft delete
- Employee avatar upload and status enum vs `terminated`
- Admin `UserAddressService` default promotion and unique generated columns
- API address grouped payload, `setDefault`, ownership 404
- `UserObserver` mail after create
- `ModulePermissionSeeder` Posts permissions exist

## How tests currently authenticate

Configurable/Bundle tests create a raw `User` and `actingAs($this->admin)` **without** `Admin` role or Super Admin access level. That is enough for feature tests that do not hit `role:` or `check.permission` middleware. It would **not** be enough to exercise this module’s admin routes.
