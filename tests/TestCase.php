<?php

declare(strict_types=1);

namespace GoCPA\SendsayLaravelMailer\Tests;

use Illuminate\Contracts\Config\Repository;
use Orchestra\Testbench\TestCase as Orchestra;
use GoCPA\SendsayLaravelMailer\SendsayMailerServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SendsayMailerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        /** @var Repository $config */
        $config = $app->make('config');

        $config->set('mail.default', 'sendsay');
        $config->set('mail.mailers.sendsay', [
            'transport' => 'sendsay',
            'account' => 'test-account',
            'apikey' => 'test-apikey',
            'proxy' => null,
            'dkimId' => null,
        ]);

        $config->set('database.default', 'sqlite');
        $config->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
