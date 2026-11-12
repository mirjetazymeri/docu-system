# Reviews — Models

Module README: [README.md](README.md)

## `App\Models\ProductReview`

Source: `app/Models/ProductReview.php`

### Purpose

One customer (or guest) review of a catalog product: rating, optional title, comment, moderation status, optional linked `User`, and guest owner-token fields.

### Table

```text
product_reviews
```

See [database.md](database.md).

### Important Fields

| Field | Type (model / migration) | Purpose |
| ----- | ------------------------ | ------- |
| `id` | bigIncrements | Primary key |
| `product_id` | unsignedBigInteger | FK to `products.id` |
| `user_id` | unsignedBigInteger, nullable | FK to `users.id` when created while `$req->user()` is present |
| `author_name` | string, nullable | Display name; from user `name` or request |
| `author_email` | string, nullable | From user `email` or request |
| `rating` | tinyInteger, cast integer | Star rating |
| `title` | string, nullable | Optional title |
| `comment` | text, nullable | Body; API store requires it |
| `status` | string(20), default `pending` | Moderation state |
| `ip` | string(45), nullable | Client IP at create |
| `user_agent` | string(512), nullable | User-Agent at create |
| `owner_edit_token` | string(64), nullable | Guest edit/delete token |
| `owner_token_expires_at` | timestamp, nullable, datetime cast | Guest token expiry |
| `edited_at` | timestamp, nullable, datetime cast | Set on API `update()` only |
| `created_at` / `updated_at` | timestamps | Laravel timestamps |

`$fillable` matches the columns above except `id` and timestamps.

The model does **not** set `$hidden`. `owner_edit_token` is therefore not hidden if the model is serialized. The API create/update/destroy responses do not return the Eloquent model; they return a small `data` array. Guest create returns `owner_token` (the token value) once.

No accessors, mutators, observers, or model events are registered for `ProductReview` (`EventServiceProvider` does not observe it).

### Casts

| Attribute | Cast |
| --------- | ---- |
| `rating` | `integer` |
| `owner_token_expires_at` | `datetime` |
| `edited_at` | `datetime` |

`status` is an uncast string.

### Relationships

| Method | Relationship | Related Model | Description |
| ------ | ------------ | ------------- | ----------- |
| `product()` | `belongsTo` | `App\Models\Product` | Catalog product |
| `user()` | `belongsTo` | `App\Models\User` | Optional account; `nullOnDelete` |

`User` has **no** `reviews()` inverse in `app/Models/User.php`.

### Scopes

| Scope | Implementation |
| ----- | -------------- |
| `scopeApproved($q)` | `$q->where('status', 'approved')` |

Admin listing does not use this scope; catalog projection filters `status = 'approved'` inline on the `reviews` relation (see [dependencies.md](dependencies.md)).

### Business Methods

None beyond Eloquent defaults and `scopeApproved`.

### Status values in code

| Surface | Values |
| ------- | ------ |
| Create (API) | `pending` or `approved` from `reviews.auto_approve` |
| Admin `STATUSES` / edit / bulk | `pending`, `approved`, `rejected`, `spam` |
| API update FormRequest `status` | `pending`, `approved`, `rejected` (no `spam`) |
| Original create-table comment | `pending\|approved\|rejected` (no `spam` in migration comment) |

`spam` is used by admin UI/validation but is not in the API update `in:` list.

---

## Related model owned by catalog-products

### `App\Models\Product::reviews()`

Source: `app/Models/Product.php`

```text
public function reviews(): HasMany
{
    return $this->hasMany(ProductReview::class);
}
```

Do not treat `Product` as owned by this module. Admin index/edit eager-load `product.translations` and call `Product::translation()` for the display name.

---

## Related model owned by auth

### `App\Models\User`

`ProductReview::user()` belongs to `User`. Store copies `$req->user()?->id`, `name`, and `email` when a user is present on the request. Policy compares `$user->id === $review->user_id`.
