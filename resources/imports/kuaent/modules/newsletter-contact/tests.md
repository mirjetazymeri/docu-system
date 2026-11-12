# Newsletter and contact — Tests

Module README: [README.md](README.md)

Search of `tests/` found **no** tests for this module.

Searched for: `Newsletter`, `NewsletterSubscriber`, `NewsletterController`, `NewsletterSubscriberController`, `NewsletterSubscriberService`, `SubscribeNewsletter`, `UnsubscribeNewsletter`, `ContactController`, `ContactFormRequest`, `ContactFormMail`, `forwardContactForm`, `newsletter_subscribers`, `admin.newsletters`, `newsletter/subscribe`, `newsletter/unsubscribe`, `/api/v1/contact`.

PHPUnit suites in `phpunit.xml`: `tests/Unit`, `tests/Feature`. Existing files cover bundles, configurables, pricing, checkout address fields, product extras, theme attributes, external catalog export, and examples. None reference newsletter or contact-form types.

There is no `NewsletterSubscriberFactory`. `phpunit.xml` sets `MAIL_MAILER=array` for the test environment; that does not substitute for feature coverage of `ContactController`.

Behavioral claims in the other docs in this folder are taken from application code, not from passing tests.
