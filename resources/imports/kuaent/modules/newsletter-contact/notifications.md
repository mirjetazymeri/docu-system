# Newsletter and contact — Mail (not Laravel Notifications)

Module README: [README.md](README.md)

`app/Notifications/` is not used by this module. Contact alerts are a Laravel **Mailable**. Newsletter subscribe/unsubscribe send **no** mail.

Shared dispatch: `App\Services\Mail\MailService` ([shared mail](../../shared/README.md#mail)). Setting keys live in [settings-system](../settings-system/README.md) (`config/settings.php` group `notifications`). Values of SMTP passwords and live addresses are not documented.

---

## `App\Mail\Admin\ContactFormMail`

Source: `app/Mail/Admin/ContactFormMail.php`

- Implements `Illuminate\Contracts\Queue\ShouldQueueAfterCommit`
- Traits: `Queueable`, `SerializesModels`

### Constructor

```text
__construct(
    public string $senderName,
    public string $senderEmail,
    string $subject,
    string $message,
)
```

`$subject` / `$message` are stored as public `$formSubject` / `$formMessage` (not as `$this->subject`, which would collide with Mailable’s subject handling).

### `envelope()`

| Field | Source |
| ----- | ------ |
| From address | `setting('notifications.mail.from_address', config('mail.from.address'))` |
| From name | `setting('notifications.mail.from_name', config('mail.from.name'))` |
| To | `[setting('notifications.admin.email', config('mail.from.address'))]` |
| Reply-To | `Address($this->senderEmail, $this->senderName)` |
| Subject | `'[Contact] ' . $this->formSubject` |

`config('mail.from.address')` / `from.name` are defined in `config/mail.php` via env **names** `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME`.

`MailService::sendNow` also calls `Mail::to($this->adminEmail())->send(...)`. `adminEmail()` is `setting('notifications.admin.email') ?: config('mail.from.address')`. Envelope `to` and `Mail::to()` are both set in code. Whether the framework delivers one copy or two is **not determinable from the current repository** (no tests).

### `content()`

View: `emails.admin.contact-form` (`resources/views/emails/admin/contact-form.blade.php`). Extends `emails.layout`.

Renders `$senderName`, `$senderEmail` (mailto link), `$formSubject`, `$formMessage` (`white-space:pre-wrap`). No phone. Copy: “Reply directly to this email to respond to the sender.”

### `attachments()`

Empty array.

---

## `MailService::forwardContactForm`

See [services.md](services.md). Summary:

| Step | Behavior |
| ---- | -------- |
| Toggle | If `setting('notifications.admin.notify_contact_form')` is falsy, return without sending |
| Send | `sendAdminNow` → `Mail::to(adminEmail())->send(new ContactFormMail(...))` |
| Errors | Caught in `sendNow`; logged; not thrown to `ContactController` |

Schema for `notifications.admin.notify_contact_form`: boolean, **default `true`**, `public` false, label `Contact Form Submission`.

The `MailService` file docblock lists contact form among “background alerts” that “are queued”. **`forwardContactForm` uses `send()`, not `queue()`.** `ShouldQueueAfterCommit` on the mailable therefore does not apply on this production path.

---

## Newsletter mail

None. No mailable is constructed from `NewsletterController` or `NewsletterSubscriberService`.

---

## Artisan test send

`app/Console\Commands\TestMailCommand.php` — signature `mail:test {type} {--to=} ...`

Type `admin-contact` (also included in `all`) sends `ContactFormMail` **synchronously**: `Mail::to($to)->send($this->makeAdminContact())`.

This **bypasses** `MailService` and the `notify_contact_form` toggle. `--to` defaults to `config('mail.from.address')`.

Sample constructor arguments in `makeAdminContact()` (fixture copy in source, not production data):

| Argument | Sample |
| -------- | ------ |
| senderName | `John Doe` |
| senderEmail | `john@example.com` |
| subject | `Question about my order` |
| message | multi-line “order confirmation” text in the command |

---

## Admin settings UI (not owned)

SMTP driver fields and the contact-form toggle are saved through admin settings (`admin.settings.*`), not through newsletter routes. Widget admin copy for `contact_form` mentions enabling Contact Form Submission under Admin → Settings → Notifications (`translations.admin.widgets.hints.contact_api`).
