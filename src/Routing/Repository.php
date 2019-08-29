<?php

namespace Rumur\Pimpled\Routing;

class Repository
{
    /** @var array  */
    protected $routes = [];

    /** @var array */
    protected $name_listed = [];

    /** @var string */
    protected static $cache_group_name = 'pmld';

    /**
     * @param Route $route
     * @return Repository
     */
    public function add(Route $route)
    {
        // Saving id by name for further use
        // with this approach we can faster get an id of route
        if ($route->hasName()) {
            $this->name_listed[$route->name()] = $route->id();
        }

        foreach ($route->methods() as $method) {
            $this->routes[$route->id()][ $method ] = $route;
        }

        return $this;
    }

    /**
     * @param string $route_id_name Route id or name
     * @param string $method        Route method such as "GET", "POST", "PUT", "DELETE", "PATCH",
     *                              if method is NOT null it checks and there is a route with such method
     * @return bool
     */
    public function has($route_id_name, $method = null)
    {
        $routes = $this->routes[$this->name_listed[$route_id_name] ?? $route_id_name];

        if ($method && !empty($routes)) {
            return isset($routes[strtoupper($method)]);
        }

        return !empty($routes);
    }

    /**
     * @param string $route_id_name Route id or name
     * @param string $method        Route method such as "GET", "POST", "PUT", "DELETE", "PATCH",
     *                              if method is null an array of routes will be returned
     * @return bool| Route[] | Route
     */
    public function get($route_id_name, $method = null)
    {
        $routes = $this->routes[$this->name_listed[$route_id_name] ?? $route_id_name];

        if (!empty($routes)) {

            if ($method) {
                $method = strtoupper($method);

                return $routes[$method] ?? false;
            }

            return $routes;
        }

        return false;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->routes;
    }

    /**
     * @return array
     */
    public function withNameOnly(): array
    {
        return array_map(function($id) {
            return current($this->get($id));
        }, $this->name_listed);
    }

    /**
     * Saves the route to a cache for a further use
     */
    public function saveRoutsWithName()
    {
        if ($routes = $this->withNameOnly()) {

            if ($cached = wp_cache_get('pmld.routing.route', static::$cache_group_name)) {

                $data = array_merge($cached, $routes);

                wp_cache_replace('pmld.routing.route', $data, static::$cache_group_name);

            } else {

                wp_cache_set('pmld.routing.route', $routes, static::$cache_group_name);
            }
        }
    }

    /**
     * @param string $name          The Route name
     * @param string $current_lang  The Current Language, it needs if it's a ML setup .e.g 'de' | 'en' | 'ru'
     * @return Route|bool
     */
    public static function getRouteByName($name, $current_lang = '')
    {
        $name_with_lang = trim("{$name}.{$current_lang}", '.');

        if ($cached = wp_cache_get('pmld.routing.route', static::$cache_group_name)) {

            if (isset($cached[$name_with_lang])) {
                return $cached[$name_with_lang];
            }

            if (isset($cached[$name])) {
                return $cached[$name];
            }
        }

        return false;
    }
}
