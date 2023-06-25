# Jetstream Plus

Jetstream Plus is a third-party package for [Laravel Jetstream](https://github.com/laravel/jetstream). It adds some extra features to the default Laravel Jetstream + Socialstream stack.

## Installation

Getting started with Jetstream Plus is a breeze. With a simple step to get you on your way to creating the next big thing. Installation step:

```sh
composer require cryptounifier/jetstream-plus
```

## Publishing Stubs

You can publish the package stubs in a simple way like Jetstream with the following command below. **However this is not necessary if your project is not new, you can look at the files inside database and stubs folder and adapt manually.**

```sh
php artisan jetstream-plus:install
```

> Note: to use 'confirm-new-location' feature with Inertia.js stack, you will to manually create a Auth page stub for it.

## What changes?

Below is the listing of what this package implements:

- Adds a Captcha class helper ([hCaptcha](https://www.hcaptcha.com/), [reCaptcha](https://developers.google.com/recaptcha?hl=pt-br), [Turnstile](https://www.cloudflare.com/pt-br/products/turnstile/), [GeeTest](https://www.geetest.com/en/) supported).
- Adds a IP address class helper ([ProxyCheck](https://proxycheck.io/), [IpRegistry](https://ipregistry.co/) supported).
- Adds a User Agent class helper (On top of [Jenssegers Agent](https://github.com/jenssegers/agent)).

Below is the listing of what this package alter/improve:

- Implements a `sign-in-notification` feature for Fortify.
- Implements a `confirm-new-location` feature for Fortify.
- Adds a captcha and proxy validation on authentication.
- Adds an image format engine for profile image.
- Adds `agent` and `location` values to sessions listing on profile page.
- Adds an `ip_address` field to user table (Check [database folder](database)).
- Adds the `ip_address` field for Fortify & Socialstream actions, and User model (Check [stubs folder](stubs)).
- Change the validation rules for Fortify & Socialstream actions to stricter rules (Check [stubs/app/Actions folder](stubs/app/Actions)).
- Adapt Jetstream default tests to comply with the stricter rules (Check [stubs/tests folder](stubs/tests)).

# License

Jetstream Plus is open-sourced software licensed under the [MIT license](LICENSE.md).
