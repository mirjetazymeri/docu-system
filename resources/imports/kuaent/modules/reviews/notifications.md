# Reviews — Mail and notifications

Module README: [README.md](README.md)

## Observed wiring

`App\Http\Controllers\Api\V1\ProductReviewController::store()` creates the review and returns JSON. It does **not** dispatch events, notifications, or mail. Source comment:

```text
// (Optional) You could dispatch a notification/event here to admins for moderation.
```

`MailService::notifyAdminNewReview()` has **no callers** in the repository except its own definition. Review create therefore does **not** send `NewReviewMail`.

Admin edit/status/bulk/delete also send no mail.

---

## `App\Mail\Admin\NewReviewMail`

Source: `app/Mail/Admin/NewReviewMail.php`

- Implements `ShouldQueueAfterCommit`.
- Uses `Queueable`, `SerializesModels`.
- Constructor payload type (PHPDoc):

```text
array{product_name: string, reviewer_name: string, rating: int, body: string}
```

### envelope()

| Field | Source |
| ----- | ------ |
| From address | `setting('notifications.mail.from_address', config('mail.from.address'))` |
| From name | `setting('notifications.mail.from_name', config('mail.from.name'))` |
| To | `[setting('notifications.admin.email', config('mail.from.address'))]` |
| Subject | `[Admin] New Product Review` |

No secrets: only setting **key names** and `config('mail.from.address')` / `config('mail.from.name')` fallbacks.

### content()

View: `emails.admin.new-review` (`resources/views/emails/admin/new-review.blade.php`). Extends `emails.layout`. Renders product, reviewer, star rating 1–5, body from `$review[...]`.

### attachments()

Empty array.

---

## `App\Services\Mail\MailService::notifyAdminNewReview`

Source: `app/Services/Mail/MailService.php` (shared mail service, not a reviews-only class).

```text
if (! setting('notifications.admin.notify_new_review')) {
    return;
}
$this->queueAdmin(new NewReviewMail($review));
```

`queueAdmin()` queues via `Mail::to($this->adminEmail())->queue($mailable)` where `adminEmail()` is `setting('notifications.admin.email') ?: config('mail.from.address')`. Failures are logged as `[MailService] Failed to queue ...`.

Class docblock states background alerts including reviews are queued with plain-array payloads.

Setting **`notifications.admin.notify_new_review`**: defined in `config/settings.php` as boolean, **default `false`**, `public` false, label `New Product Review`.

Because nothing calls `notifyAdminNewReview()`, toggling that setting has no effect on review create in the current code.

---

## Artisan test send

`app/Console/Commands/TestMailCommand.php` — signature `mail:test {type} {--to=} ...`

Type `admin-new-review` (also included in `all`) sends `NewReviewMail` **synchronously**: `Mail::to($to)->send($this->makeAdminNewReview())`.

Sample payload in `makeAdminNewReview()` (not production data):

```text
product_name  => Wireless Headphones XM5
reviewer_name => Jane Smith
rating        => 4
body          => Great sound quality, comfortable fit. Battery could be better.
```

`--to` defaults to `config('mail.from.address')`. The mailable envelope still sets its own `to` from settings; actual recipients of a queued/sent mailable with both `Mail::to()` and envelope `to` follow Laravel mail behavior. **Not further specified in this module’s code.**

This command is a shared mail tester, not a reviews-module command.

---

## Setting keys (names only)

From `NewReviewMail` and `MailService` / `config/settings.php`:

| Setting key | Role |
| ----------- | ---- |
| `notifications.mail.from_address` | Mailable from address |
| `notifications.mail.from_name` | Mailable from name |
| `notifications.admin.email` | Admin recipient |
| `notifications.admin.notify_new_review` | Gate inside `notifyAdminNewReview` (unwired from store) |

Do not document SMTP passwords or `.env` values. SMTP fields live under `notifications.mail.*` in settings (settings module).

---

## Laravel Notifications

No `Illuminate\Notifications\Notification` class for product reviews was found. `User` uses `Notifiable` for other auth mails, not for reviews.
