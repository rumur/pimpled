<?php

namespace Rumur\Pimpled\Routing;

/**
 * Class ApiRouter
 *
 * @author rumur
 */
class ApiRouter extends Router
{
    /**
     * The callback e.g. @see home_url() | site_url() | rest_url() | etc...
     *
     * @var callable
     */
    protected $host_resolver_cb = 'rest_url';

    /** @inheritDoc */
    public function addRoute($route, array $args)
    {
        return parent::addRoute(sprintf('%s/%s', $this->prefix(), $route), $args);
    }

    /**
     * Register all routes.
     *
     * @link https://developer.wordpress.org/reference/functions/register_rest_route/
     *
     * @param Route $route
     */
    public function registerRoute(Route $route)
    {
        $args = array_intersect_key($route->toArray(),
            array_fill_keys(['callback', 'methods', 'args', 'permission_callback'], ' ')
        );

        register_rest_route($this->prefix(), str_replace($this->prefix(), '', $route->route()), $args);
    }
}
