<?php

namespace GoCPA\SendsayLaravelMailer;

use Illuminate\Support\Facades\Mail;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SendsayMailerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('sendsay-laravel-mailer')
            ->hasConfigFile('sendsay-laravel-mailer');
    }

    public function packageBooted()
    {
        Mail::extend('sendsay', function () {
            $config = $this->app['config']->get('sendsay-laravel-mailer', []);
            $account = $config['account'] ?? null;
            $apikey = $config['apikey'] ?? null;

            return new SendsayMailerTransport($account, $apikey);
        });
    }
}
