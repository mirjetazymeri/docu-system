# Rental — commands

## `rentals:release-expired-holds`

Class: `App\Console\Commands\ReleaseExpiredRentalHolds`.

Laravel 12 auto-discovers `app/Console/Commands`. Registered for the scheduler via `bootstrap/app.php` `withRouting(commands: routes/console.php)`.

```
php artisan rentals:release-expired-holds
    {--dry-run : Show the count without releasing}
```

Description: `Cancel expired rental holds so abandoned carts free the booking calendar`.

### Query

`RentalReservation` where:

- `status` = `RentalReservation::STATUS_HELD` (`held`)
- `expires_at` is not null
- `expires_at` `<` `now()`

### Behaviour

| `--dry-run` | Effect |
|---|---|
| set | Count only; no `update` |
| omitted | If count > 0, `update(['status' => cancelled])` |

Does **not** delete rows, **not** clear cart lines, **not** rebuild projections.

Stdout: `Would release {n} expired rental hold(s).` or `Released {n} expired rental hold(s).` Exit `Command::SUCCESS`.

Cancelled is not in `ACTIVE_STATUSES`, so the calendar frees immediately for overlap queries.

Confirmed rows have `expires_at` nulled at place; this command never touches them.

### Schedule (verified)

`routes/console.php`:

```php
Schedule::command('rentals:release-expired-holds')->everyTenMinutes();
```

Comment in that file: free the booking calendar from abandoned rental carts. This is the **only** scheduled command in `routes/console.php` besides the default `inspire` Artisan closure.

No other rental commands in `app/Console/Commands`.
