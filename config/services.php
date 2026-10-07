<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // The public (live) address of this site, e.g. https://crms.example.gov.ph.
    // It is written into the QR codes that open the CSF survey on a visitor's
    // phone, so they must hold an address a phone can reach. Left empty, the
    // codes hold the address the page was opened with: right on the live
    // server, but a local name (crms_csf.test, an office IP) on a test or
    // kiosk machine.
    'csf' => [
        'public_url' => env('CSF_PUBLIC_URL'),
    ],

];
