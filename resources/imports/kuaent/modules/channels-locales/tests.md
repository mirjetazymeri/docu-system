# Tests

There are **no** PHPUnit tests whose subject is `ChannelController`, `LocaleController`, `TranslationController`, `Api\V1\LocaleController`, `ChannelLocaleService`, `ResolveApiChannel`, `SetLocaleFromRequest`, `SetUserLocale`, or `SetCurrentChannel`.

`tests/` was searched for those class names, `X-Channel`, `X-Locale`, `channel_locales`, and `channel_locale_codes`. No dedicated matches.

## Tests that instantiate `Channel` / `Locale` as fixtures

These are catalog/checkout tests. They create a channel and locale so storefront services can resolve context.

### `Tests\Feature\Bundle\BundleTestCase`

Path: `tests/Feature/Bundle/BundleTestCase.php`

- Skips unless `DB_CONNECTION` is `mysql` (comment: production migrations are MySQL-only).
- `Locale::query()->create(['code' => 'en', 'name' => 'English', 'is_active' => true, 'is_default' => true])`
- `Channel::query()->create(['name' => 'Default', 'code' => 'default', 'domain' => 'example.test'])`
- User with `current_channel_id` and `channels()->attach`
- `app()->instance('currentChannel', $this->channel)`
- `app()->instance('currentChannelId', $this->channel->id)` — this key is **not** bound by this module’s middleware
- `app()->setLocale('en')`

Extending tests:

- `tests/Feature/Bundle/BundleProductTest.php`
- `tests/Feature/Bundle/BundleCartCheckoutTest.php`

### `Tests\Feature\Configurable\ConfigurableTestCase`

Path: `tests/Feature/Configurable/ConfigurableTestCase.php`

Same Channel/Locale/`currentChannel`/`currentChannelId`/`setLocale('en')` pattern. Also `Attribute::channels()->attach($this->channel->id)`.

Extending tests:

- `tests/Feature/Configurable/ConfigurableProductTest.php`
- `tests/Feature/Configurable/ConfigurableCartTest.php`

## PHPUnit defaults

`phpunit.xml` sets `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`. The Feature tests above `markTestSkipped` on that driver.

`CACHE_STORE=array` in phpunit env; locale API cache keys are not asserted.

## Factories

No `ChannelFactory` or `LocaleFactory` under `database/factories/`.
