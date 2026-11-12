# Newsletter and contact — Database

Module README: [README.md](README.md) · Model: [models.md](models.md)

This module creates one table. No later migration in `database/migrations` alters `newsletter_subscribers`.

There is no contact-form table. Submissions are not stored.

## `newsletter_subscribers`

**Migration:** `database/migrations/2026_04_09_120000_create_newsletter_subscribers_table.php`

`up()`: `Schema::create('newsletter_subscribers', ...)`.

`down()`: `Schema::dropIfExists('newsletter_subscribers')`.

| Column | Type | Constraints / defaults |
| ------ | ---- | ---------------------- |
| `id` | bigint PK | |
| `channel_id` | FK → `channels.id` | `constrained()`, `cascadeOnDelete()` |
| `name` | string | nullable (Laravel default string length 255) |
| `email` | string | required at DB (not nullable) |
| `status` | string(32) | default `'subscribed'` |
| `source` | string(32) | nullable |
| `unsubscribe_token` | string(64) | **unique** (global, not per channel) |
| `subscribed_at` | timestamp | nullable |
| `unsubscribed_at` | timestamp | nullable |
| `meta` | json | nullable |
| `created_at` / `updated_at` | timestamps | |

Indexes / uniqueness:

| Index | Columns |
| ----- | ------- |
| Unique | `(channel_id, email)` |
| Unique | `unsubscribe_token` |
| Index | `(channel_id, status)` |

Status and source are **not** database enums. Application writers use `subscribed` / `unsubscribed` and sources `api` / `admin` ([README.md](README.md)).

FormRequest `max:191` on `name` / `email` is tighter than the 255-character string columns.

`unsubscribe_token` is minted as `(string) Str::uuid()` (36 characters) when missing.

## Foreign keys owned elsewhere

`channel_id` references `channels` from [channels-locales](../channels-locales/README.md) (`database/migrations/2026_03_17_153207_create_channels_table.php`). This module does not migrate `channels`.

## Seeders

No seeder inserts `newsletter_subscribers` rows.

`database/seeders/ModulePermissionSeeder.php` creates module display name `Newsletters` and permissions `newsletter_view`, `newsletter_create`, `newsletter_edit`, `newsletter_delete` (`guard_name` `web`). That seeder does not insert subscribers.
