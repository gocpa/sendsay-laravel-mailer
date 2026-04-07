<?php

declare(strict_types=1);

return [
    'apikey' => env('MAIL_SENDSAY_APIKEY'),
    'account' => env('MAIL_SENDSAY_ACCOUNT'),
    'proxy' => env('MAIL_SENDSAY_PROXY'),
    'dkimId' => env('MAIL_SENDSAY_DKIM_ID'),
];
