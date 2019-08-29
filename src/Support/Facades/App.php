<?php

namespace Rumur\Pimpled\Support\Facades;

/**
 * Class App
 *
 * @method static string basePath()
 * @method static boolean isDebug() Determine if an application can use debug mode.
 * @method static boolean isDevelopment() Determine if application is in local environment.
 *
 * @see \Rumur\Pimpled\Foundation\Application
 *
 * @author rumur
 */
class App extends Facade
{
    /** @inheritdoc */
    protected static function getFacadeAccessor()
    {
        return 'app';
    }
}
