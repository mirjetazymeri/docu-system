# Settings & system database

Migrations this module requires. Filenames are as on disk.

## `settings`

Created and altered by four migrations.

### Create

**File:** `database/migrations/2026_03_17_130440_create_settings_table.php`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigint PK | |
| `key` | string | **unique** at create time (later dropped) |
| `value` | text nullable | |
| `type` | string nullable | comment in migration: `text, file, json, color` — unused by `SettingService` |
| `created_at` / `updated_at` | timestamps | |

### Channel column

**File:** `database/migrations/2026_03_17_153619_add_channel_id_to_settings_table.php`

| Column | Type | Constraints |
|--------|------|-------------|
| `channel_id` | nullable FK → `channels.id` | `constrained()`, `cascadeOnDelete()` |

### Composite unique

**File:** `database/migrations/2026_03_17_172207_add_unique_kwy_to_settings_table.php`

Index name: `settings_key_channel_unique` on `(key, channel_id)`.

### Drop old unique on `key`

**File:** `database/migrations/2026_03_17_174843_fix_settings_unique_constraint.php`

`dropUnique(['key'])`. Comment in the file notes the drop may fail depending on the actual index name (`settings_key_unique` is mentioned as a safer fallback but is **not** executed).

After a successful migrate, uniqueness is **`(key, channel_id)`**, not `key` alone. `channel_id` is nullable; SQL NULL uniqueness behavior is engine-dependent and is not further constrained in PHP.

### Entity relationship

```
channels 1 ──< settings (key + channel_id unique)
```

Deleting a channel cascade-deletes its settings rows.

There is **no settings seeder** in `database/seeders/`. Missing keys resolve from `config/settings.php` `default` (not `value`) via `SettingService::get()`.

File uploads are stored on the `public` disk under directory `settings/` (path string in `value`, not a media-library row).

## `admin_theme_settings`

**File:** `database/migrations/2026_08_17_000100_create_admin_theme_settings_table.php`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigint PK | |
| `options` | JSON nullable | cast to array on the model |
| `created_at` / `updated_at` | timestamps | |

No FK, no unique on `options`. Application convention: at most one logical theme; `AdminThemeService` uses `latest('id')`.

Default option keys live in `config/admin_theme.php` → `defaults`, not in SQL.

## Cache keys (not tables)

These are Laravel cache entries, not database tables (unless `CACHE_STORE=database`):

| Key pattern | Written / forgotten by |
|-------------|------------------------|
| `app_settings_channel_{channelId}` | `SettingService::all()` / `clearCache()` |
| `api.settings.v1.channel_{channelId}` | API `SettingController` + `SettingService::clearCache()` |
| `admin_theme_options` | `AdminThemeService::all()` / `save()` / `reset()` |

`SystemCacheService::clearStorefrontDataCaches()` also forgets catalog/locale keys owned by other modules — see [services.md](services.md).
