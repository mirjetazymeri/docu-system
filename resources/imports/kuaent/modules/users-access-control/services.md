# Services

Back to [README](README.md).

This module has one service class. Admin address writes go through it. The storefront API does **not**.

---

## `App\Services\UserAddressService`

Source: `app/Services/UserAddressService.php`

### Responsibility

Create / update / delete `UserAddress` rows and keep default shipping/billing flags consistent for one `user_id`.

### Dependencies

`App\Models\UserAddress`, `Illuminate\Support\Facades\DB`.

### Called by

`App\Http\Controllers\Admin\UserAddressController` (`store`, `update`, `delete`).

### Calls

Eloquent `UserAddress` only. No events, jobs, or mail.

### Transaction boundaries

Every public method wraps work in `DB::transaction(...)`.

---

### `create(array $data): UserAddress`

1. `clearConflictingDefaults($data['user_id'], $data)`
2. `UserAddress::create($data)`
3. `enforceDefaults($addr)`
4. `return $addr->refresh()`

### `update(UserAddress $addr, array $data): UserAddress`

Same as create, using `$addr->user_id` and excluding `$addr->id` when clearing.

### `delete(UserAddress $addr): void`

1. Remember `user_id` and whether the row was default shipping/billing
2. `$addr->delete()` (SoftDeletes)
3. If it was default shipping → `ensureOneDefault($userId, 'is_default_shipping')`
4. Same for billing

Queries in `ensureOneDefault` use the default SoftDeletes scope (soft-deleted row is gone from those queries).

---

### `enforceDefaults(UserAddress $addr)` (protected)

If the saved row is default shipping, set `is_default_shipping = false` on all **other** rows for that user (non-trashed). Same for billing.

Then `ensureOneDefault` for both flags: if the user has **no** row with the flag true, take `latest('id')` remaining address and `forceFill` the flag true, then clear others.

So the first address created for a user becomes both defaults even if the request left the checkboxes off.

### `clearConflictingDefaults(int $userId, array $data, ?int $exceptId)` (protected)

If the incoming payload has a truthy default flag, updates **`withTrashed()`** rows for that user (except `$exceptId`) to false. That clears flags on soft-deleted rows as well, which matters because generated unique columns `default_shipping_user` / `default_billing_user` still exist on soft-deleted rows.

---

## Not a service here

| Logic | Location |
| ----- | -------- |
| Access-level permission check | `User::hasAccessLevelPermission`, `AutoAuthorizePermission`, `AuthServiceProvider` |
| Storefront address defaults | `AddressController` private methods (duplicate, slightly different) |
| Role assignment | Controllers + seeders + auth/sales-specialist |
