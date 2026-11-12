# Commands

## `gdpr:purge-telemetry`

**Class:** `App\Console\Commands\PurgeStorefrontTelemetryCommand`  
**File:** `app/Console/Commands/PurgeStorefrontTelemetryCommand.php`

Laravel auto-discovers `app/Console/Commands`. Signature:

```
gdpr:purge-telemetry
    {--days= : Override retention days for every channel}
    {--dry-run : Show counts without deleting}
```

Description string: `Purge old storefront telemetry records according to GDPR retention settings`.

### Schedule

**Not scheduled.** `routes/console.php` contains only:

```php
Schedule::command('rentals:release-expired-holds')->everyTenMinutes();
```

`bootstrap/app.php` registers that file via `withRouting(..., commands: .../routes/console.php)`. There is no other `Schedule::` call in this repository for `gdpr:purge-telemetry`.

### Behavior (`handle(SettingService $settings): int`)

1. `$dryRun = (bool) $this->option('dry-run')`.
2. `$forcedDays = $this->option('days')`.
3. For **each** `Channel` (`select id, code`):
   - Retention days: if `--days` is numeric → `max(1, (int) $forcedDays)`; else `max(1, (int) $settings->get('gdpr.analytics_retention_days', 180, (int) $channel->id))`.
   - `$cutoff = now()->subDays($days)`.
   - **Events:** `channel_id` = channel, `occurred_at` `<` cutoff.
   - **Consents:** `channel_id` = channel, `withdrawn_at` **not null**, `withdrawn_at` `<` cutoff.
   - Count both queries. If not dry-run, `delete()` both query builders.
   - Print: `[{code}] retention={days}d events={eventCount} consents={consentCount}`
4. Info line: `Dry run: …` or `Deleted: …` with total event and consent counts.
5. Return `self::SUCCESS`.

Active consents (`withdrawn_at` null) are **never** removed by this command. Events older than the cutoff are removed regardless of the row’s `consent` flag.

`--days` applies the **same** integer to every channel (ignores per-channel `gdpr.analytics_retention_days`).

### Examples (also in root [README.md](../../../README.md))

```bash
php artisan gdpr:purge-telemetry --dry-run
php artisan gdpr:purge-telemetry
php artisan gdpr:purge-telemetry --days=90
```
