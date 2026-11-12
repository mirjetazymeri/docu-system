# Reviews — Controllers

Module README: [README.md](README.md) · Routes: [routes.md](routes.md) · API: [api.md](api.md)

---

## `App\Http\Controllers\Api\V1\ProductReviewController`

Source: `app/Http/Controllers/Api/V1/ProductReviewController.php`

### Responsibility

Storefront JSON API: create a review on an active product; update or delete a review as the owner (policy) or as a guest with a valid owner token.

### Dependencies

Constructor: none (no injected services). Uses `StoreProductReviewRequest`, `UpdateProductReviewRequest`, `Product`, `ProductReview`, `config('reviews.*')`, `Illuminate\Support\Str`, `$this->authorize()`.

### Authentication note (observed)

Review routes sit in the public `v1` group (`channel.api`, `locale.api`) and are **not** inside the `auth:sanctum` group in `routes/api.php`. The controller uses `$req->user()` (default guard), not `$req->user('sanctum')`. Other public storefront controllers on the same group (for example cart/wishlist) resolve optional Sanctum users via `user('sanctum')`. Whether a Sanctum bearer token populates `$req->user()` here is **not determinable from the current repository** (no review feature tests). Guest token flow does not depend on that.

### Methods

#### `store(StoreProductReviewRequest $req, string $idOrSku)`

- **Route:** `POST /api/v1/products/{idOrSku}/reviews`
- **Auth:** FormRequest `authorize()` — user present **or** `reviews.guests_enabled`.
- **Permissions:** none.
- **Validation:** [StoreProductReviewRequest](requests-validation.md).
- **Behavior:**
  1. `resolveProduct($idOrSku)` — 404 if not found/active.
  2. Optional uniqueness when `reviews.single_per_product` — 422 message `You already reviewed this product.`
  3. Status `approved` if `reviews.auto_approve`, else `pending`.
  4. Create row: product, optional `user_id`, author from user or input, rating/title/comment, status, `ip`, `user_agent`.
  5. If no user: set `owner_edit_token` = `Str::uuid()->toString()`, `owner_token_expires_at` = now + `reviews.guest_token_ttl_days` (default 14).
- **Events / mail / jobs:** none dispatched. Comment in source: optional admin notification/event is not implemented. `MailService::notifyAdminNewReview()` is not called.
- **Response:** HTTP 201 JSON:

```text
data.id
data.status
data.message   // "Thanks! Your review is live." | "Thanks! Your review is pending moderation."
data.owner_token       // null if user present; else token
data.token_expires_at  // null if user present; else ISO-8601 from owner_token_expires_at
```

#### `update(UpdateProductReviewRequest $req, int $id)`

- **Route:** `PUT` and `PATCH` `/api/v1/reviews/{id}` (same method).
- **Auth:** If `$req->user()`: `$this->authorize('update', $review)` → [ProductReviewPolicy](permissions.md). Else `guestTokenValid()` or 403 `{ "message": "Forbidden" }`.
- **Validation:** [UpdateProductReviewRequest](requests-validation.md).
- **Behavior:** Updates `rating`, `title`, `comment` from `$req->only(...)`. If user `can('review_edit')` and `status` is filled, includes `status`. Always sets `edited_at` = now. `findOrFail` → 404.
- **Response:** JSON `data.id`, `data.status`, `data.message` = `Review updated`.

#### `destroy(Request $req, int $id)`

- **Route:** `DELETE /api/v1/reviews/{id}`
- **Auth:** Same split as update (`delete` policy vs guest token).
- **Validation:** none.
- **Behavior:** `$review->delete()` (hard delete).
- **Response:** JSON `data.id` (path id), `data.message` = `Review deleted`.

#### `resolveProduct(string $idOrSku): Product` (protected)

Query `Product` where `status = 'active'`. If `is_numeric($idOrSku)`, filter `products.id`. Else: `sku` **or** translation slug for `app()->getLocale()` **or** any translation slug. `firstOrFail()`. Locale is whatever `locale.api` set. Channel is **not** applied in this query.

#### `guestTokenValid(Request $req, ProductReview $review): bool` (protected)

Token = header `X-Review-Owner-Token` or query `owner_token`. False if token or `owner_edit_token` empty. `hash_equals` compare. If `owner_token_expires_at` is non-null and `now()` is greater, false.

---

## `App\Http\Controllers\Admin\ProductReviewController`

Source: `app/Http/Controllers/Admin/ProductReviewController.php`

### Responsibility

Blade admin: filterable Yajra DataTables list, edit form, status change, bulk actions, delete.

### Dependencies

No constructor injection. Uses `UpdateProductReviewRequest`, `ProductReview`, `Request`, `DB`, `Str`, `Yajra\DataTables`. `Product` is imported in the file; methods use `$row->product` / `$review->product`, not the `Product` class name.

`STATUSES = ['pending','approved','rejected','spam']`.

Constructor middleware ([permissions.md](permissions.md)):

| Permission | Methods |
| ---------- | ------- |
| `review_view` | `index` |
| `review_edit` | `edit`, `update`, `setStatus`, `bulk` |
| `review_delete` | `delete` |

Route group also requires `auth`, `verified.if-required`, `set.user.locale`, `role:Admin`.

### Methods

#### `index(Request $request)`

- **Route:** `GET admin/reviews` name `admin.reviews.index`
- **Non-AJAX:** status counts (`groupBy status`), total count, view `admin.reviews.index` with filters `q`, `status`, `rating`, `productId`, `from`, `to`.
- **AJAX (DataTables):** Eloquent query with `product.translations`, `user`. Filters:
  - `q` LIKE on `title`, `comment`, `author_name`, `author_email`
  - `status` if in `STATUSES`
  - `rating` integer 1–5
  - `product_id` if truthy integer
  - `from` / `to` as `whereDate(created_at)`
- Columns: checkbox, product HTML (or deleted-product translation), title/comment, author, star string, status badge, `created_at` `Y-m-d H:i`, actions partial.
- The index Blade form does **not** include a `product_id` field; the controller still reads `product_id`. Filter UI in the view: `q`, `status`, `rating`, `from`, `to`, plus status tab buttons.

#### `edit(int $id)`

- **Route:** `GET admin/reviews/edit/{id}` name `admin.reviews.edit`
- `findOrFail` with `product.translations`, `user`. View `admin.reviews.edit`.

#### `update(UpdateProductReviewRequest $request, int $id)`

- **Route:** `PUT admin/reviews/{id}` name `admin.reviews.update`
- `fill($request->validated())` + `save()`. Does **not** set `edited_at`.
- Redirect `admin.reviews.edit` with flash `Review updated.`

#### `setStatus(Request $request, int $id)`

- **Route:** `POST admin/reviews/{id}/status` name `admin.reviews.status`
- Validates `status` required, `in:pending,approved,rejected,spam`.
- Sets `status`, save, `back()` flash `Status set to {status}.`
- Row actions Blade posts `approved` or `pending` only (toggle approve/unapprove).

#### `bulk(Request $request)`

- **Route:** `POST admin/reviews/bulk` name `admin.reviews.bulk`
- Validates `ids` array min 1, `ids.*` integer exists `product_reviews,id`, `action` in `approve,reject,spam,delete`.
- `DB::transaction`: `delete` → `ProductReview::whereIn('id', $ids)->delete()`; else map action to status `approved` / `rejected` / `spam` and `update(['status' => ...])`.
- `back()` flash `Bulk action completed.`

#### `delete(int $id)`

- **Route:** `DELETE admin/reviews/{id}` name `admin.reviews.delete`
- `findOrFail` + `delete()`. Redirect `admin.reviews.index` flash `Review deleted.`
