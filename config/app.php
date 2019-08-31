<?php

use function Rumur\Pimpled\Support\app;

defined('ABSPATH') or die();

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
        Rumur\Pimpled\Mail\MailServiceProvider::class,
        Rumur\Pimpled\View\ViewServiceProvider::class,
        Rumur\Pimpled\Scheduling\SchedulingServiceProvider::class,
        Rumur\Pimpled\Notifications\Notice\NoticeService::class,
//        Rumur\Pimpled\Foundation\Asset\AssetService::class,
    ], apply_filters('pmld.config.app_providers', [
        Pmld\App\Providers\AppServiceProvider::class,
        Pmld\App\Providers\RouteServiceProvider::class,
//        Rumur\Pimpled\App\Migrations\Aggregator::class,
//        Rumur\Pimpled\App\Assets\Aggregator::class,
    ] ) ),

    /*
    |--------------------------------------------------------------------------
    | The Application Hooks
    |--------------------------------------------------------------------------
    |
    | This is the list of Hooks.
    |
    */
    'hooks' => [
        Pmld\App\Hooks\DummyHook::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | The Application Server Side rendered Gutenberg blocks
    |--------------------------------------------------------------------------
    |
    | This is the list of Blocks.
    |
    */
    'blocks' => [
        // ...
    ],

    /*
    |--------------------------------------------------------------------------
    | The Path to the validation messages file.
    |--------------------------------------------------------------------------
    */
    'validation' => app()->langPath('validation.php'),
];
