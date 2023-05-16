<?php

namespace GoCPA\SendsayLaravelMailer\Tests;

use GoCPA\SendsayLaravelMailer\SendsayMailerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            SendsayMailerServiceProvider::class,
        ];
    }
}
