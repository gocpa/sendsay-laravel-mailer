<?php

declare(strict_types=1);

namespace GoCPA\SendsayLaravelMailer;

use Illuminate\Support\Facades\Mail;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class SendsayMailerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('sendsay-laravel-mailer')
            ->hasConfigFile('sendsay-laravel-mailer');
    }

    public function bootingPackage(): void
    {
        Mail::extend('sendsay', function (array $config): SendsayMailerTransport {
            return new SendsayMailerTransport(
                account: (string) ($config['account'] ?? config('sendsay-laravel-mailer.account', '')),
                apikey: (string) ($config['apikey'] ?? config('sendsay-laravel-mailer.apikey', '')),
                proxy: $this->stringOrNull($config['proxy'] ?? config('sendsay-laravel-mailer.proxy')),
                dkimId: $this->stringOrNull($config['dkimId'] ?? config('sendsay-laravel-mailer.dkimId'))
            );
        });
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }
}
