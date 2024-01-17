<?php

declare(strict_types=1);

// config for GoCPA/SendsayLaravelMailer
return [
    'apikey' => env('MAIL_SENDSAY_APIKEY'),
    'account' => env('MAIL_SENDSAY_ACCOUNT'),
    'proxy' => env('MAIL_SENDSAY_PROXY'),
    'dkimId' => env('MAIL_SENDSAY_DKIM_ID'),
];
