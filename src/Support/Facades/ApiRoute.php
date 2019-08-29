<?php

namespace Rumur\Pimpled\Support\Facades;

use Rumur\Pimpled\Routing\Resolver;

/**
 * Class Route
 *
 * @method static self any(string $route, array $args): self
 * @method static self delete(string $route, array $args): self
 * @method static self get(string $route, array $args): self
 * @method static self match(array $methods, $route, array $args)
 * @method static self patch(string $route, array $args): self
 * @method static self post(string $route, array $args): self
 * @method static self put(string $route, array $args): self
 *
 * @see \Rumur\Pimpled\Routing\Router
 */
class ApiRoute extends Facade
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
        return 'api.route';
    }
}
