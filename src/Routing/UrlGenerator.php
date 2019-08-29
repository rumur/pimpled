<?php

namespace Rumur\Pimpled\Routing;

use Rumur\Pimpled\Container\Container;
use Rumur\Pimpled\Contracts\Routing\Route;
use Rumur\Pimpled\Contracts\Support\Arrayable;

class UrlGenerator
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * All available routes
     *
     * @var array
     */
    protected $routes = [];

    /**
     * UrlGenerator constructor.
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @param array $routes
     *
     * @return $this
     */
    public function setRoutes(array $routes): self
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * @param $name
     * @param Arrayable | array $parameters
     * @param string $locale
     * @return mixed|string
     */
    public function route($name, $parameters = [], $locale = null)
    {
        $locale = $locale ?: $this->app->getLocale(true);

        /** @var \Rumur\Pimpled\Contracts\Routing\Route $route */
        $name_with_locale = trim("{$name}.{$locale}", '.');

        if ($this->has($name_with_locale)) {
            $route = $this->get($name_with_locale);
        } elseif ($this->has($name)) {
            $route = $this->get($name);
        } else {
            $route = new NullRoute;
        }

        return $this->urlFromRoute($route, $parameters);
    }

    /**
     * Makes an URL from Route and passed parameters
     *
     * @param Route $route
     * @param Arrayable | array $parameters
     *
     * @return string
     */
    public function urlFromRoute(Route $route, $parameters = []): string
    {
        $parameters = array_filter($parameters instanceof Arrayable ? $parameters->toArray() : (array)$parameters);

        if ($route->hasParams()) {
            return $route->urlFromParams($parameters);
        }

        return add_query_arg($parameters, $route->uri());
    }

    /**
     * Determine if the route is present in the stack
     *
     * @param $name
     * @return bool
     */
    public function has($name): bool
    {
        return isset($this->routes[$name]);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->routes[$name];
    }
}
