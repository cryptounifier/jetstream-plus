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

## What changes?

Below is the listing of what this package implements:

- Adds a Captcha class helper ([hCaptcha](https://www.hcaptcha.com/) supported).
- Adds a IP address class helper ([ProxyCheck](https://proxycheck.io/) supported).
- Adds a User Agent class helper (On top of [Jenssegers Agent](https://github.com/jenssegers/agent)).
- Adds user ban system (On top of [Laravel Ban](https://github.com/cybercog/laravel-ban)).

Below is the listing of what this package alter/improve:

- Adds a `sign-in-notification` feature for Fortify.
- Adds a `not-banned` middleware.
- Adds a captcha and proxy validation on authentication.
- Adds an image format engine for profile image.
- Adds `agent` and `location` values to sessions listing on profile page.
- Adds an `ip_address`, `banned_at` field to user table (Check [database folder](database)).
- Adds the `ip_address` field for Fortify & Socialstream actions, and User model (Check [stubs folder](stubs)).
- Change the validation rules for Fortify & Socialstream actions to stricter rules (Check [stubs/app/Actions folder](stubs/app/Actions)).
- Adapt Jetstream default tests to comply with the stricter rules (Check [stubs/tests folder](stubs/tests)).

# License

Jetstream Plus is open-sourced software licensed under the [MIT license](LICENSE.md).
