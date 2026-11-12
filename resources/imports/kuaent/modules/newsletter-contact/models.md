# Newsletter and contact — Models

Module README: [README.md](README.md) · Schema: [database.md](database.md)

## `App\Models\NewsletterSubscriber`

Source: `app/Models/NewsletterSubscriber.php`

### Purpose

One newsletter subscription row per **channel + email**. Status is `subscribed` or `unsubscribed` in application code. Optional name, source, unsubscribe token, timestamps, and JSON `meta`.

### Table

```text
newsletter_subscribers
```

No `$table` override. Default Laravel timestamps. Does **not** use `SoftDeletes`, `HasFactory`, hidden attributes, or appends.

### Fillable

`channel_id`, `name`, `email`, `status`, `source`, `unsubscribe_token`, `subscribed_at`, `unsubscribed_at`, `meta`.

### Casts

| Attribute | Cast |
| --------- | ---- |
| `meta` | `array` |
| `subscribed_at` | `datetime` |
| `unsubscribed_at` | `datetime` |

### Relations

| Method | Type | Target | Notes |
| ------ | ---- | ------ | ----- |
| `channel()` | `belongsTo` | `App\Models\Channel` | FK `channel_id`. `Channel` has **no** inverse `hasMany` for subscribers |

### Scopes

| Method | Behavior |
| ------ | -------- |
| `scopeForChannel($query, ?int $channelId)` | `where('channel_id', $channelId)` |

Admin list/edit/delete use `forChannel((int) auth()->user()->current_channel_id)`. Passing `null` still emits `where channel_id = null` (PHP `?int` allows it; admin always casts to `int`).

### Constants / accessors

None. Status and source are plain strings.

### Related models (not owned)

| Model | Role |
| ----- | ---- |
| `App\Models\Channel` | Parent sales channel. Deleting a channel cascade-deletes these rows ([database.md](database.md)) |
| `App\Models\User` | Not related on this model. Admin scoping uses `User::current_channel_id` / `currentChannel()` |

### Factories

There is no `NewsletterSubscriberFactory` under `database/factories`.
