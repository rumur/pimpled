<?php

namespace Rumur\Pimpled\Routing;

/**
 * Class Router
 *
 * @author rumur
 */
abstract class Router
{
    /**
     * Alias for GET transport method.
     *
     * @var string
     */
    const READABLE = 'GET';

    /**
     * Alias for POST transport method.
     *
     * @var string
     */
    const CREATABLE = 'POST';

    /**
     * Alias for POST, PUT, PATCH transport methods together.
     *
     * @var string
     */
    const EDITABLE = 'POST, PUT, PATCH';

    /**
     * Alias for DELETE transport method.
     *
     * @var string
     */
    const DELETABLE = 'DELETE';

    /**
     * Alias for GET, POST, PUT, PATCH & DELETE transport methods together.
     *
     * @var string
     */
    const ALLMETHODS = 'GET, POST, PUT, PATCH, DELETE';

    /** @var string */
    protected $prefix;

    /** @var Repository */
    protected $repository;

    /** @var Resolver */
    protected $resolver;

    /**
     * The callback e.g. @see home_url() | site_url() | rest_url() | etc...
     *
     * @var callable
     */
    protected $host_resolver_cb = 'network_site_url';

    /**
     * Router constructor.
     *
     * @param string $prefix
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;

        $this->boot();
    }

    /**
     * Factory
     *
     * @param $namespace
     *
     * @return static
     */
    public static function make($namespace)
    {
        return new static($namespace);
    }

    /**
     * Boot the Router
     */
    protected function boot()
    {
        $this->useResolver(new Resolver)
            ->useRepository(new Repository);

        do_action_ref_array('pmld.routing.router_boot', [&$this]);
    }

    /**
     * Registers the route with GET, POST, PUT, PATCH, DELETE methods.
     *
     * @param $route
     * @param array $args
     * @return $this
     */
    public function any($route, array $args)
    {
        return $this->addRoute($route, array_merge($args, ['methods' => static::ALLMETHODS]));
    }

    /**
     * Registers the route with a GET method.
     *
     * @param $route
     * @param array $args
     * @return $this
     */
    public function get($route, array $args)
    {
        return $this->addRoute($route, array_merge($args, ['methods' => static::READABLE]));
    }

    /**
     * Registers the route with a POST method.
     *
     * @param $route
     * @param array $args
     * @return $this
     */
    public function post($route, array $args)
    {
        return $this->addRoute($route, array_merge($args, ['methods' => static::CREATABLE]));
    }

    /**
     * Registers the route with a DELETE method.
     *
     * @param $route
     * @param array $args
     * @return $this
     */
    public function delete($route, array $args)
    {
        return $this->addRoute($route, array_merge($args, ['methods' => static::DELETABLE]));
    }

    /**
     * Registers the route with a PUT method.
     *
     * @param $route
     * @param array $args
     * @return $this
     */
    public function put($route, array $args)
    {
        return $this->addRoute($route, array_merge($args, ['methods' => 'PUT']));
    }

    /**
     * Registers the route with a PATCH method.
     *
     * @param $route
     * @param array $args
     * @return $this
     */
    public function patch($route, array $args)
    {
        return $this->addRoute($route, array_merge($args, ['methods' => 'PATCH']));
    }

    /**
     * Registers the route with a PATCH method.
     *
     * @param array $methods
     * @param $route
     * @param array $args
     * @return $this
     */
    public function match(array $methods, $route, array $args)
    {
        $args['methods'] = $methods;

        return $this->addRoute($route, $args);
    }

    /**
     * Adds route
     *
     * @param string $route
     * @param array $args
     *
     * @return $this
     */
    public function addRoute($route, array $args)
    {
        $args = array_merge([
            'methods' => static::ALLMETHODS,
        ], $args);

        $route = new Route($route, $args, $this->resolver);

        $route->setHostResolverCallback($this->hostResolver());

        $this->repository()->add(apply_filters('pmld.routing.add_route', $route, $args, $this));

        return $this;
    }

    /**
     * All routes
     *
     * @return Route[]
     */
    public function getRoutes()
    {
        return apply_filters('pmld.routing.routes', $this->repository()->all(), $this);
    }

    /**
     * @param callable $resolver
     * @return $this
     */
    public function useHostResolver($resolver)
    {
        if (is_callable($resolver)) {
            $this->host_resolver_cb = $resolver;
        }

        return $this;
    }

    /**
     * @param Repository $repository
     *
     * @return $this
     */
    public function useRepository(Repository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @param Resolver $resolver
     *
     * @return $this
     */
    public function useResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * Sets up the host resolver callback
     * that helps make a URL from a route.
     *
     * @return callable
     */
    public function hostResolver(): callable
    {
        return $this->host_resolver_cb;
    }

    /**
     * @return Repository
     */
    public function repository(): Repository
    {
        return $this->repository;
    }

    /**
     * @return string
     */
    public function prefix(): string
    {
        return $this->prefix;
    }

    /**
     * Register all routes.
     *
     * @return $this
     */
    public function registerRoutes()
    {
        $routes = $this->getRoutes();

        foreach ($routes as $methods) {
            foreach ($methods as $route) {
                $this->registerRoute($route);
            }
        }

        // Saving routes for further use
        // E.g. if we need them somewhere in our app
        $this->repository()->saveRoutsWithName();

        return $this;
    }

    /**
     * Registers Route to the system.
     *
     * @param Route $route
     * @return mixed
     */
    abstract protected function registerRoute(Route $route);
}
