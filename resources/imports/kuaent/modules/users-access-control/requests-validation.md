# Requests & Validation

Back to [README](README.md).

Dedicated FormRequests exist only for **admin user addresses**. Other admin screens validate inline on `Illuminate\Http\Request`. API addresses validate inside `AddressController`.

`authorize()` on the address FormRequests returns `true`; permission is enforced by `check.permission` middleware.

---

## FormRequests

### `App\Http\Requests\Admin\UserAddress\StoreUserAddressRequest`

Source: `app/Http/Requests/Admin/UserAddress/StoreUserAddressRequest.php`

`prepareForValidation()`: merge `is_default_shipping` / `is_default_billing` via `$this->boolean(...)`.

| Field | Rules |
| ----- | ----- |
| `user_id` | required, integer, `exists:users,id` |
| `label` | nullable, string, max 100 |
| `first_name` | required, string, max 100 |
| `last_name` | required, string, max 100 |
| `company` | nullable, string, max 150 |
| `nui` | nullable, string, max 100, **`required_with:company`** |
| `phone` | nullable, string, max 50 |
| `address_line1` | required, string, max 191 |
| `address_line2` | nullable, string, max 191 |
| `city` | required, string, max 120 |
| `state_code` | nullable, string, max 60 |
| `postcode` | nullable, string, max 40 |
| `country_code` | required, string, size 2, `exists:countries,code` |
| `is_default_shipping` | sometimes, boolean |
| `is_default_billing` | sometimes, boolean |
| `meta` | nullable, array |

Admin Blade `_form` has `company` but **no `nui` input**. Filling company without `nui` fails validation.

### `App\Http\Requests\Admin\UserAddress\UpdateUserAddressRequest`

Source: `app/Http/Requests/Admin/UserAddress/UpdateUserAddressRequest.php`

Empty subclass of `StoreUserAddressRequest` — same rules, including required `user_id` (address can be reassigned).

---

## Inline: `UserController`

### Create (POST)

| Field | Rules |
| ----- | ----- |
| `name` | required, string, max 255 |
| `email` | required, email, unique `users,email` |
| `password` | required, string, min 6, confirmed |
| `roles` | array |
| `access_levels` | array |
| `groups` | array |
| `groups.*` | integer, exists `user_groups,id` |

Not validated: `roles.*`, `access_levels.*`, `channels` / `channels.*`.

### Edit (POST)

Same, except `email` unique ignore current id; `password` **nullable** min 6 confirmed.

---

## Inline: `RoleController`

| Action | Rules |
| ------ | ----- |
| create | `name` required, unique `roles,name` |
| edit | `name` required, unique `roles,name,{id}` |

---

## Inline: `PermissionController`

| Action | Rules |
| ------ | ----- |
| store | `name` required unique `permissions,name`; `module_id` required exists `modules,id` |
| update | `name` unique ignore current; `module_id` required exists |

`guard_name` is not input; always written as `web`.

---

## Inline: `ModuleController`

| Action | Rules |
| ------ | ----- |
| store | `name` required unique `modules,name` |
| update | `name` unique ignore current |

`description` is saved via `only('name','description')` with **no** validation rule.

---

## Inline: `AccessLevelController`

| Field | Rules |
| ----- | ----- |
| `name` | required, unique `access_levels,name` (ignore id on update) |
| `description` | nullable, string |
| `permissions` | array |
| `permissions.*` | exists `permissions,id` |

---

## Inline: `UserGroupController`

| Field | Create | Update |
| ----- | ------ | ------ |
| `name` | required, string, max 255, unique `user_groups,name` | unique ignore id |
| `slug` | nullable, string, max 255, unique | unique ignore id |
| `description` | nullable, string | same |
| `is_active` | sometimes, boolean | same |
| `users` | array | same |
| `users.*` | integer, exists `users,id` | same |

Create: omitted `is_active` → stored `true`. Update: omitted → stored `false` (`(bool)($validated['is_active'] ?? false)`).

---

## Inline: `EmployeeController::validatedData`

| Field | Rules |
| ----- | ----- |
| `first_name` | required, string, max 100 |
| `last_name` | required, string, max 100 |
| `email` | required, email, max 255, unique `employees,email` ignore id on update |
| `phone` | nullable, string, max 30 |
| `position` | nullable, string, max 120 |
| `department` | nullable, string, max 120 |
| `hired_at` | nullable, date |
| `salary` | **required**, numeric, min 0 |
| `status` | required, `in:active,inactive,terminated` |
| `avatar` | nullable, image, max 2048 (KB) |

Migration `phone` is unbounded string; `status` enum does not include `terminated`; `salary` is nullable in DB.

---

## API: `AddressController::rules`

Source: `app/Http/Controllers/Api/V1/AddressController.php`

Uses **`address1` / `address2`**, then `normalizeAddressColumns()` maps to `address_line1` / `address_line2`.

Field presence depends on `App\Support\CheckoutAddressFields` (settings). Hidden fields are `nullable` + `prohibited`.

| Field | When shown | Notes |
| ----- | ---------- | ----- |
| `first_name` / `last_name` | always required | max 100 |
| `address1` | always required | max 255 |
| `country_code` | always required | size 2, exists `countries,code`; uppercased in `mergeGroupedPayload` |
| `label` | if `show('label')` | else prohibited |
| `company` | if `show('company')` | max 191 |
| `nui` | if `show('nui')` | `required_with:company` if `nuiRequiredIfCompany()` |
| `phone` | if `show('phone')` | required vs nullable via `required('phone')` |
| `state_code` | if `show('state_code')` | max **10** (admin allows 60) |
| `city` | if `show('city')` | required vs nullable; max **100** (admin 120) |
| `postcode` | if `show('postcode')` | max **32** (admin 40) |
| `address2` | if `show('address2')` | max 255 |
| `is_default_shipping` / `is_default_billing` | sometimes boolean | |

`mergeGroupedPayload()` also accepts nested `personal`, `company` (object with `name`/`nui`), `address`, `defaults.billing` / `defaults.shipping`.

### `setDefault`

| Field | Rules |
| ----- | ----- |
| `type` | required, `in:shipping,billing,both` |

---

## Auth / sales-specialist (consumers)

Not owned here; they create `User` rows:

- `AuthController::register` — name, email unique, password `Password::min(8)->letters()->mixedCase()->numbers()` confirmed, optional locale
- `StoreCustomerRequest` — name, email unique, optional phone
- Fortify `CreateNewUser` — auth module
