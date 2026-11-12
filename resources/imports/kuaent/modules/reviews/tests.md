# Reviews — Tests

Module README: [README.md](README.md)

## Search performed

Searched `tests/` (all `*Test*.php` and content) for `review` / `ProductReview` (case-insensitive).

**No unit, feature, or API tests reference product reviews.**

Existing tests in the repository cover other domains (configurable/bundle catalog, pricing, checkout address fields, etc.). None map to `ProductReviewController`, `ProductReviewPolicy`, `StoreProductReviewRequest`, or `config/reviews.php`.

| Test | Type | Covers |
| ---- | ---- | ------ |
| *(none)* | | |

## Scenarios apparently not tested

After searching `tests/`, these behaviors have **no** automated coverage in this repository:

- Guest create, owner token return, update/delete with header vs query token, expiry
- Authenticated create, `user_id` / author copy from user
- `guests_enabled` false
- `single_per_product` 422 for user vs email
- `auto_approve` status
- Policy owner vs `review_edit` / `review_delete`
- API `status` gated on `review_edit`
- Inactive product 404
- Admin DataTables filters, bulk transaction, spam status
- `MailService::notifyAdminNewReview` (and the fact store does not call it)

Postman collection folder **Product Reviews** is a manual API collection, not a PHPUnit test.

## How to run tests (repo-wide)

This module adds no test command. Application tests: `php artisan test` or `vendor/bin/phpunit` from the project root (standard Laravel). No review filter exists.
