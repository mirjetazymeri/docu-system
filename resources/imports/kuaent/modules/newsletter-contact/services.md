# Newsletter and contact — Services

Module README: [README.md](README.md)

Owned here: `App\Services\NewsletterSubscriberService`.

`App\Services\Mail\MailService` is owned by [shared mail](../../shared/README.md#mail). This page documents only the method `ContactController` calls.

Neither class is bound in a service provider; Laravel auto-wires them.

---

## `App\Services\NewsletterSubscriberService`

Source: `app/Services/NewsletterSubscriberService.php`

Uses `App\Models\NewsletterSubscriber`, `App\Models\Channel`, `Illuminate\Support\Str`.

### `subscribe(string $email, ?string $name = null, ?int $channelId = null, string $source = 'api', array $meta = []): NewsletterSubscriber`

1. `$channelId = resolveChannelId($channelId)`
2. `$email = normalizeEmail($email)` (`Str::lower(trim(...))`)
3. `NewsletterSubscriber::firstOrNew(['channel_id' => $channelId, 'email' => $email])`
4. `fill`:
   - `name` → `$name ?: $subscriber->name` (empty string keeps the previous name)
   - `status` → `'subscribed'`
   - `source` → `$source ?: $subscriber->source` (`'api'` and `'admin'` are always truthy, so those callers overwrite `source`)
   - `subscribed_at` → `now()`
   - `unsubscribed_at` → `null`
   - `meta` → `array_merge` of existing array `meta` (or `[]` if not an array) with `$meta`
5. If `unsubscribe_token` is empty, set `(string) Str::uuid()`
6. `save()`; return `$subscriber->fresh()`

Callers:

| Caller | name | channelId | source | meta |
| ------ | ---- | --------- | ------ | ---- |
| `NewsletterController::subscribe` | validated `name` (may be `''`) | current channel id | `'api'` | `ip`, `user_agent` |
| `NewsletterSubscriberController::store` | `$data['name'] ?: null` | admin `channelId()` | `'admin'` | omitted (`[]`) |

### `unsubscribeByEmail(string $email, ?int $channelId = null): ?NewsletterSubscriber`

Resolve channel + normalize email. `first()` where `channel_id` and `email`. Missing → `null`. Else `markUnsubscribed`.

### `unsubscribeByToken(string $token): ?NewsletterSubscriber`

`where('unsubscribe_token', trim($token))->first()`. **No** `channel_id` filter. Missing → `null`. Else `markUnsubscribed`.

### `markUnsubscribed(NewsletterSubscriber $subscriber): NewsletterSubscriber` (protected)

`forceFill`:

- `status` → `'unsubscribed'`
- `unsubscribed_at` → existing value or `now()` (does not bump the timestamp on a second unsubscribe)

`save()`; return `fresh()`. Does not clear `subscribed_at` or rotate the token.

### `resolveChannelId(?int $channelId = null): int` (protected)

1. If `$channelId` is truthy, return it. **`0` is treated as missing.**
2. Else if `app()->bound('currentChannel')` and `app('currentChannel')?->id`, return that id as int.
3. Else `(int) Channel::query()->orderBy('id')->value('id')`.

If there is no channel row, `value('id')` is `null` and this returns `0`. Saving `channel_id = 0` against `channels.id` is **not covered by tests** (FK would fail if the insert runs).

Admin `store()` passes `$this->channelId()`. If that is `0`, this method falls through to `currentChannel` / first `Channel` by id — which can differ from `index`/`edit`/`delete` (`forChannel(0)`). After `SetCurrentChannel` ([channels-locales](../channels-locales/README.md)), a logged-in admin with at least one channel normally has a non-zero `current_channel_id`.

### `normalizeEmail(string $email): string` (protected)

`Str::lower(trim($email))`.

---

## `App\Services\Mail\MailService::forwardContactForm`

Source: `app/Services/Mail/MailService.php`

Signature: `forwardContactForm(string $senderName, string $senderEmail, string $subject, string $message): void`.

```text
if (! setting('notifications.admin.notify_contact_form')) {
    return;
}
$this->sendAdminNow(new ContactFormMail($senderName, $senderEmail, $subject, $message));
```

`sendAdminNow` → `sendNow($this->adminEmail(), $mailable)` → `Mail::to($to)->send($mailable)` inside try/catch. Failures are logged as `[MailService] Failed to send {class}: {message}` and **not** rethrown.

`adminEmail()`: `(string) (setting('notifications.admin.email') ?: config('mail.from.address'))`.

The class docblock says contact form mail is queued. **This method uses `send()`, not `queue()`.** See [notifications.md](notifications.md).
