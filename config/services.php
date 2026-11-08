<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'python_api' => [
        'url' => env('PYTHON_API_URL', 'http://host.docker.internal:8101'),
        'timeout' => (int) env('PYTHON_API_TIMEOUT', 30),
    ],

];
