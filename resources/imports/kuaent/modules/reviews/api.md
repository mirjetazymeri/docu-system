# Reviews — Storefront API

Module README: [README.md](README.md) · Validation: [requests-validation.md](requests-validation.md) · Controllers: [controllers.md](controllers.md)

Shared v1 headers/query (group middleware, not review-specific): `X-Channel` or `?channel=`, `X-Locale` / `?locale=` / `Accept-Language`. See `ResolveApiChannel` and `SetLocaleFromRequest`. Review handlers do not read channel for product lookup.

Client notes also exist in `postman_collection.json` folder **Product Reviews**. That collection’s PUT description says status is applied when the user `can('manage_reviews')`. **Source of truth is the controller:** status is applied when `$req->user()?->can('review_edit')`. There is no `manage_reviews` permission in `ModulePermissionSeeder`.

There is no API Resource class; responses are inline arrays.

---

## `POST /api/v1/products/{idOrSku}/reviews`

### Purpose

Create a review on an **active** product.

### Authentication

Optional. `StoreProductReviewRequest::authorize()` requires `$this->user()` **or** `config('reviews.guests_enabled')` (default true).

See [controllers.md](controllers.md) for `$req->user()` vs `user('sanctum')`.

### Permissions

None.

### Request

**Path**

| Param | Description |
| ----- | ----------- |
| `idOrSku` | Numeric product id, or SKU, or translation slug (current locale first, then any locale) among `status = active` products |

**JSON body**

| Field | Required | Notes |
| ----- | -------- | ----- |
| `rating` | yes | integer, config `reviews.rating` (default 1–5) |
| `title` | no | string, max 255 |
| `comment` | yes | string, max 5000 |
| `author_name` | guests | max 255 |
| `author_email` | guests | `email:rfc,dns`, max 255 |

Logged-in create copies `name` / `email` from the user; guest identity fields are ignored for author columns when a user is present (`$req->user()?->name ?? $req->input('author_name')`).

### Validation

See `StoreProductReviewRequest`.

### Response

HTTP **201**:

| Path | Meaning |
| ---- | ------- |
| `data.id` | New review id |
| `data.status` | `pending` or `approved` |
| `data.message` | Live vs pending copy (see controller) |
| `data.owner_token` | Guest UUID token, else `null` |
| `data.token_expires_at` | Guest ISO-8601 expiry, else `null` |

Exact JSON shape is defined in `store()` return. A stored example (guest, pending) appears in `postman_collection.json` as a 201 sample; treat live `id` / token / dates as runtime values.

### Possible errors

| Status | Condition |
| ------ | --------- |
| 403 | `authorize()` false (guests disabled and no user) |
| 404 | Product not found or not `active` (`firstOrFail`) |
| 422 | Validation failure, **or** `single_per_product` duplicate (`You already reviewed this product.`) |

### Side effects

- Insert into `product_reviews`.
- No event, no `MailService::notifyAdminNewReview()`, no projection rebuild in this controller. Catalog rating aggregates update when projection is rebuilt elsewhere (`ProductProjectionService` reads approved reviews).

---

## `PUT /api/v1/reviews/{id}` and `PATCH /api/v1/reviews/{id}`

Same handler: `ProductReviewController::update`.

### Purpose

Update rating/title/comment; optionally status for users with `review_edit`.

### Authentication

- If `$req->user()`: `ProductReviewPolicy::update` (owner or `review_edit`).
- Else: valid guest owner token (header `X-Review-Owner-Token` or query `owner_token`).

### Permissions

Policy / `review_edit` for status change. Guests cannot set status through the `can('review_edit')` branch without a user.

### Request

**Path:** `id` — review primary key (`findOrFail`).

**Headers:** `X-Review-Owner-Token` (guests).

**Query:** `owner_token` (alternative to header).

**JSON body** (all optional / sometimes):

| Field | Rules |
| ----- | ----- |
| `rating` | integer between config min/max |
| `title` | nullable string max 255 |
| `comment` | string max 5000 |
| `status` | `pending\|approved\|rejected` — applied only with `review_edit` |

### Response

JSON `data.id`, `data.status`, `data.message` = `Review updated`.

### Possible errors

| Status | Condition |
| ------ | --------- |
| 403 | Policy deny, or guest token invalid/expired (`Forbidden`) |
| 404 | Review missing |
| 422 | Validation |

AuthorizationException from `authorize()` uses Laravel’s default (typically 403). Exact JSON for policy denial is framework default, not customized in this controller.

### Side effects

- Updates allowed columns; sets `edited_at` = now.
- No mail.

---

## `DELETE /api/v1/reviews/{id}`

### Purpose

Hard-delete a review.

### Authentication

Same as update, using `ProductReviewPolicy::delete` or guest token.

### Request

Path `id`. Guest token header/query as above. No body.

### Response

JSON `data.id` (the path id), `data.message` = `Review deleted`.

### Possible errors

| Status | Condition |
| ------ | --------- |
| 403 | Policy deny or invalid guest token |
| 404 | Missing review |

### Side effects

- Row deleted. Product `cascadeOnDelete` is the inverse (product delete removes reviews), not this action.

---

## What this API does not expose

- List or show reviews for a product.
- PDP `reviews` key: `ProductProjectionDetailResource` sets `'reviews' => null`. Rating summary on projection is `rating.avg` / `rating.count` from approved reviews (catalog-products).
