<?php

namespace Pmld\Support\Facades;

/**
 * Class App
 *
 * @method static string basePath()
 * @method static boolean isDebug() Determine if an application can use debug mode.
 * @method static boolean isDevelopment() Determine if application is in local environment.
 *
 * @see \Pmld\Plugin | \Pmld\Foundation\Application
 *
 * @author  rumur
 */
class App extends Facade
{
    /** @inheritdoc */
    protected static function getFacadeAccessor()
    {
        return 'app';
    }
}
