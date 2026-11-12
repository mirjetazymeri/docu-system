# Reviews — Requests and validation

Module README: [README.md](README.md)

## `App\Http\Requests\Api\V1\StoreProductReviewRequest`

Source: `app/Http/Requests/Api/V1/StoreProductReviewRequest.php`

### authorize()

```text
return $this->user() !== null || (bool) config('reviews.guests_enabled', true);
```

If guests are disabled and there is no `$this->user()`, Laravel rejects the request (typically 403). Config default for `guests_enabled` is `true`.

### rules()

`$min` / `$max` from `config('reviews.rating.min', 1)` and `config('reviews.rating.max', 5)`.

| Field | Rules | When |
| ----- | ----- | ---- |
| `rating` | required, integer, between `$min`,`$max` | always |
| `title` | nullable, string, max 255 | always |
| `comment` | required, string, max 5000 | always |
| `author_name` | required, string, max 255 | only if `!$this->user()` |
| `author_email` | required, `email:rfc,dns`, max 255 | only if `!$this->user()` |

`email:rfc,dns` performs a DNS check on the domain. Failure mode is Laravel’s standard validation error for that rule.

### messages()

| Key | Message |
| --- | ------- |
| `author_name.required` | `Name is required for guest reviews.` |
| `author_email.required` | `Email is required for guest reviews.` |

No `prepareForValidation` / `after` hooks.

---

## `App\Http\Requests\Api\V1\UpdateProductReviewRequest`

Source: `app/Http/Requests/Api/V1/UpdateProductReviewRequest.php`

### authorize()

Always `true`. Comment in source: authorization is in the controller (policy or guest token).

### rules()

Same rating min/max config as store.

| Field | Rules |
| ----- | ----- |
| `rating` | sometimes, integer, between `$min`,`$max` |
| `title` | sometimes, nullable, string, max 255 |
| `comment` | sometimes, string, max 5000 |
| `status` | sometimes, `in:pending,approved,rejected` |

`status` is **not** `spam`. Controller still only applies `status` if `$req->user()?->can('review_edit')` and the field is filled.

---

## `App\Http\Requests\Admin\ProductReview\UpdateProductReviewRequest`

Source: `app/Http/Requests/Admin/ProductReview/UpdateProductReviewRequest.php`

### authorize()

Always `true`. Comment: already behind Admin middleware.

Admin permission is `check.permission:review_edit` on `update`, not this FormRequest.

### rules()

| Field | Rules |
| ----- | ----- |
| `author_name` | required, string, max 191 |
| `author_email` | required, email, max 191 |
| `rating` | required, integer, min 1, max 5 |
| `title` | nullable, string, max 255 |
| `comment` | nullable, string (no max) |
| `status` | required, string, `Rule::in(['pending','approved','rejected','spam'])` |

Admin rating bounds are hard-coded, not `config('reviews.rating')`. Admin `author_email` uses `email` without `rfc,dns`.

---

## Controller inline validation

### Admin `setStatus`

| Field | Rules |
| ----- | ----- |
| `status` | required, string, `in:pending,approved,rejected,spam` |

### Admin `bulk`

| Field | Rules |
| ----- | ----- |
| `ids` | required, array, min 1 |
| `ids.*` | integer, `exists:product_reviews,id` |
| `action` | required, string, `in:approve,reject,spam,delete` |

---

## Comparison notes (facts)

- API store requires `comment`; admin update allows nullable `comment`; DB column is nullable.
- API guest `author_email` max 255 + `email:rfc,dns`; admin max 191 + `email`.
- API update `status` omits `spam`; admin includes `spam`.
