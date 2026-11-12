# Database

Two tables. No seeders insert consent or event rows. No factories under `database/factories`. Permissions for the admin UI are seeded in `database/seeders/ModulePermissionSeeder.php` (module display name `Storefront Events`), not as telemetry data.

---

## `storefront_consents`

**Migration:** `database/migrations/2026_04_09_183000_create_storefront_consents_table.php`

`down()`: `Schema::dropIfExists('storefront_consents')`.

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint PK | |
| `channel_id` | FK nullable | `constrained()` → `channels`; `nullOnDelete()` |
| `user_id` | FK nullable | `constrained()` → `users`; `nullOnDelete()` |
| `visitor_id` | string 191 nullable | indexed |
| `session_id` | string 191 nullable | indexed |
| `consent_analytics` | boolean | default `false` |
| `consent_marketing` | boolean | default `false` |
| `consent_source` | string 120 nullable | controller default stored value `cookie_banner` when request field empty |
| `policy_version` | string 80 nullable | |
| `preferences` | json nullable | |
| `ip_hash` | char 64 nullable | indexed; SHA-256 hex of `{ip}\|{app.key}` (see [controllers.md](controllers.md)); raw IP is not a column |
| `user_agent` | string 512 nullable | |
| `consented_at` | timestamp nullable | indexed |
| `withdrawn_at` | timestamp nullable | indexed |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

Composite indexes:

| Name | Columns |
|------|---------|
| `storefront_consents_channel_user_idx` | `channel_id`, `user_id` |
| `storefront_consents_channel_visitor_idx` | `channel_id`, `visitor_id` |
| `storefront_consents_channel_session_idx` | `channel_id`, `session_id` |

There is **no** unique constraint on `(channel_id, user_id)`, visitor, or session. `firstOrNew` / `latest('id')` assume at most one logical row per identity; concurrent posts can create duplicates.

---

## `storefront_events`

**Create:** `database/migrations/2026_04_09_180000_create_storefront_events_table.php`  
**Alter:** `database/migrations/2026_07_14_150000_add_entity_time_index_to_storefront_events_table.php`

Create `down()`: `dropIfExists('storefront_events')`. Alter `down()`: drop index `storefront_events_channel_entity_time_idx`.

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint PK | |
| `channel_id` | FK nullable | `constrained()`; `nullOnDelete()` |
| `user_id` | FK nullable | `constrained()`; `nullOnDelete()` |
| `visitor_id` | string 191 nullable | indexed |
| `session_id` | string 191 nullable | indexed |
| `event_type` | string 50 | indexed |
| `event_name` | string 120 nullable | |
| `entity_type` | string 120 nullable | trending widget filters `product` |
| `entity_id` | string 120 nullable | trending widget requires numeric string |
| `url` | text nullable | validation max 2048 on API |
| `referrer` | text nullable | validation max 2048 on API |
| `search_term` | string 255 nullable | required on API when `event_type=search` |
| `payload` | json nullable | |
| `consent` | boolean | default `false`; snapshot at ingest, not a live join to consent |
| `ip_hash` | char 64 nullable | indexed; same hash formula as consents |
| `user_agent` | string 512 nullable | |
| `occurred_at` | timestamp nullable | indexed; purge filter; ingest default `now()` |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

Indexes:

| Name | Columns |
|------|---------|
| `storefront_events_channel_type_time_idx` | `channel_id`, `event_type`, `occurred_at` |
| `storefront_events_channel_entity_time_idx` | `channel_id`, `entity_type`, `entity_id`, `occurred_at` (second migration) |

Plus single-column indexes on `visitor_id`, `session_id`, `event_type`, `ip_hash`, `occurred_at`.

No unique constraint. Admin can delete individual rows (`delete()`). Privacy delete and purge also hard-delete.

---

## Related tables (not owned)

| Table | Relation |
|-------|----------|
| `channels` | FK parent; purge iterates `Channel::query()->select(['id', 'code'])` |
| `users` | FK parent; privacy filters `user_id` |
| `settings` | `key` / `value` / `channel_id` via `SettingService` (schema in `config/settings.php` group `gdpr`) |
