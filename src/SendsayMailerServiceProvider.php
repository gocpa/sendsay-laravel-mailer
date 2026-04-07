<?php

declare(strict_types=1);

namespace GoCPA\SendsayLaravelMailer;

use InvalidArgumentException;
use Illuminate\Support\Facades\Mail;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SendsayMailerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('sendsay-laravel-mailer')
            ->hasConfigFile('sendsay-laravel-mailer');
    }

    public function packageBooted(): void
    {
        Mail::extend('sendsay', function () {
            $config = $this->app['config']->get('sendsay-laravel-mailer', []);
            $account = $this->getRequiredConfig($config, 'account');
            $apikey = $this->getRequiredConfig($config, 'apikey');
            $proxy = $this->getOptionalConfig($config, 'proxy');
            $dkimId = $this->getOptionalConfig($config, 'dkimId');

            return new SendsayMailerTransport($account, $apikey, $proxy, $dkimId);
        });
    }

    /**
     * @param array<string, mixed> $config
     */
    private function getRequiredConfig(array $config, string $key): string
    {
        $value = $config[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException(sprintf('Sendsay mailer config "%s" must be a non-empty string.', $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function getOptionalConfig(array $config, string $key): ?string
    {
        $value = $config[$key] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException(sprintf('Sendsay mailer config "%s" must be a string or null.', $key));
        }

        return $value;
    }
}
