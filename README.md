# Неофициальный Laravel драйвер для Sendsay

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gocpa/sendsay-laravel-mailer.svg?style=flat-square)](https://packagist.org/packages/gocpa/sendsay-laravel-mailer)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/gocpa/sendsay-laravel-mailer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/gocpa/sendsay-laravel-mailer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/gocpa/sendsay-laravel-mailer/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/gocpa/sendsay-laravel-mailer/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/gocpa/sendsay-laravel-mailer.svg?style=flat-square)](https://packagist.org/packages/gocpa/sendsay-laravel-mailer)

Неофициальный драйвер Laravel Mailer для отправки писем через Sendsay

## Installation

You can install the package via composer:

```bash
composer require gocpa/sendsay-laravel-mailer
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="GoCPA\\SendsayLaravelMailer\\SendsayMailerServiceProvider"
```

This is the contents of the published config file:

```php
return [
    'apikey' => env('MAIL_SENDSAY_APIKEY'),
    'account' => env('MAIL_SENDSAY_ACCOUNT'),
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Tony V](https://github.com/vaninanton)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
