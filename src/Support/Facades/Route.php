<?php

namespace Rumur\Pimpled\Support\Facades;

/**
 * Class Route
 *
 * @method static self useWPML()
 * @method static self usePolyLang()
 *
 * @see \Rumur\Pimpled\Routing\Router
 */
class Route extends ApiRoute
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
        return 'web.route';
    }
}
