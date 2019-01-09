<?php

defined('ABSPATH') or die();

use function \Pmld\app;

return [
    /*
    |--------------------------------------------------------------------------
    | App current Mode
    |--------------------------------------------------------------------------
    |
    | Example:
    |   "development" | "local" | "production"
    |
    */

    'env' => 'development',

    /*
    |--------------------------------------------------------------------------
    | App Directory URI
    |--------------------------------------------------------------------------
    |
    | This is the web server URI to your theme directory.
    |
    | Example:
    |   https://example.com/app/plugins/your_plugin_name
    |
    */

    'uri' => app()->getPublicUrl(),

    /*
    |--------------------------------------------------------------------------
    | The Service Providers.
    |--------------------------------------------------------------------------
    |
    | This is the list of ServiceProviders.
    |
    */

    'providers' => array_merge( [
        \Pmld\Notifications\Notice\NoticeService::class,
        \Pmld\Foundation\Http\RequestService::class,
        \Pmld\Foundation\Http\ResponseService::class,
        \Pmld\Providers\RestRoutesService::class,
        \Pmld\Foundation\Asset\AssetService::class,
    ], apply_filters('pmld.app_service_providers', [
        \Pmld\App\Migrations\Aggregator::class,
    ] ) ),

    /*
    |--------------------------------------------------------------------------
    | The Path to the validation messages file.
    |--------------------------------------------------------------------------
    */
    'validation' => app()->langPath('validation.php'),
];
