# Sendsay Laravel Mailer

Production-ready Laravel mail transport driver for Sendsay API.

## Compatibility

- PHP: `8.1` to `8.5`
- Laravel: `10` to `13`

## Installation

```bash
composer require gocpa/sendsay-laravel-mailer
```

## Publish Config

```bash
php artisan vendor:publish --provider="GoCPA\SendsayLaravelMailer\SendsayMailerServiceProvider" --tag="sendsay-laravel-mailer-config"
```

## .env Example

```dotenv
MAIL_MAILER=sendsay
MAIL_SENDSAY_ACCOUNT=your-account
MAIL_SENDSAY_APIKEY=your-api-key
MAIL_SENDSAY_PROXY=
MAIL_SENDSAY_DKIM_ID=
```

## `config/mail.php` Example

```php
'default' => env('MAIL_MAILER', 'smtp'),

'mailers' => [
    'sendsay' => [
        'transport' => 'sendsay',
        'account' => env('MAIL_SENDSAY_ACCOUNT'),
        'apikey' => env('MAIL_SENDSAY_APIKEY'),
        'proxy' => env('MAIL_SENDSAY_PROXY'),
        'dkimId' => env('MAIL_SENDSAY_DKIM_ID'),
    ],
],
```

## Testing

```bash
composer test
```

## Changelog

Please see repository releases and tags for changelog details.

## Contributing

Contributions are welcome. Please open an issue first for major changes.

## Security

If you discover a security issue, please contact the maintainers privately.

## License

The MIT License (MIT). Please see `LICENSE.md` for details.
