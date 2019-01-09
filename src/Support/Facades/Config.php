<?php

namespace Pmld\Support\Facades;

/**
 * Class Config
 *
 * @method static bool   has($key)
 * @method static mixed  get($key, $default = null)
 * @method static array  getMany($keys)
 * @method static void   set($key, $value = null)
 * @method static void   prepend($key, $value)
 * @method static void   push($key, $value)
 * @method static array  all()
 *
 * @see \Pmld\Config\Repository
 */
class Config extends Facade
{
    /**
     * Return the service provider key responsible for the request class.
     * The key must be the same as the one used when registering
     * the service provider.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'config';
    }
}
