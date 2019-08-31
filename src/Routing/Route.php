<?php

namespace Rumur\Pimpled\Routing;

use Closure;
use InvalidArgumentException;
use Rumur\Pimpled\Contracts\Routing\Route as RouteContract;
use Rumur\Pimpled\Contracts\Support\Arrayable;
use Rumur\Pimpled\Support\Traits\DigestArrayableData;

/**
 * Class Route
 *
 * @package Routing
 *
 * @author rumur
 */
class Route implements RouteContract
{
    use DigestArrayableData;

    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $path;

    /** @var string */
    protected $route;

    /** @var string */
    protected $uri;

    /** @var array */
    protected $args = [];

    /** @var array */
    protected $methods = [];

    /** @var array */
    protected $regexp = [];

    /**
     * Params that were parsed and which it's expecting to see
     *
     * @var array
     */
    protected $params = [];

    /** @var Resolver */
    protected $resolver;

    /** @var Closure|array */
    protected $callback;

    /**
     * Parameters that could be used when the route is
     * represented as a string.
     *
     * @var array
     */
    protected $parameters = [];

    /** @var Closure|array */
    protected $permission_callback;

    /**
     * When this feature is enabled parseRegexp is gonna
     * to try to make params have names within the regexp
     *
     * @var bool
     */
    protected $use_named_regexp = true;

    /**
     * The callback e.g. @see home_url() | site_url() | rest_url() | etc...
     *
     * @var callable
     */
    protected $host_resolver_cb = 'network_site_url';

    /**
     * Route constructor.
     *
     * @param string $path
     * @param array $args
     * @param Resolver $resolver
     */
    public function __construct(string $path, array $args, Resolver $resolver)
    {
        $this->path = $path;

        $this->useArgs($args);
        $this->setResolver($resolver);
    }

    /**
     * @return string
     */
    public function id(): string
    {
        if (!$this->id) {
            $this->id = md5($this->route());
        }

        return $this->id;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function methods(): array
    {
        return $this->methods;
    }

    /**
     * @return bool
     */
    public function hasName(): bool
    {
        return !empty($this->name());
    }

    /**
     * @param string|array $method
     *
     * @return bool
     */
    public function hasMethod($method): bool
    {
        return !empty(array_intersect($this->methods(), $this->parseMethods($method)));
    }

    /**
     * @return string
     */
    public function uri(): string
    {
        if (!$this->uri && is_callable($this->host_resolver_cb)) {

            $this->uri = call_user_func($this->host_resolver_cb, $this->path());

            if (!is_string($this->uri)) {
                throw new InvalidArgumentException(sprintf('The %s must return a string', print_r($this->host_resolver_cb, true)));
            }
        }

        return $this->uri;
    }

    /**
     * Should be function that can build the URL from the route
     *
     * @param callable $cb
     *
     * @return $this
     */
    public function setHostResolverCallback(callable $cb)
    {
        $this->host_resolver_cb = $cb;

        return $this;
    }

    /**
     * @return array
     */
    public function params(): array
    {
        return array_keys($this->params);
    }

    /**
     * @return array
     */
    public function paramsWithUrlPlaceholder(): array
    {
        return $this->params;
    }

    /**
     * @return bool
     */
    public function hasParams(): bool
    {
        return !empty($this->params());
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function useArgs(array $args = [])
    {
        $rest_support = [
            'args' => [],
            'permission_callback' => '',
        ];

        $args = wp_parse_args($args, array_merge([
            'name' => false,
            'regexp' => [],
            'methods' => ['GET'],
            'callback' => false,
        ], $rest_support));

        $this->name = sanitize_text_field($args['name']);
        $this->methods = $this->parseMethods($args['methods']);
        $this->callback = $this->parseCallback($args['callback']);

        $parsed_regexp = $this->parseRegexp($args['regexp']);

        $this->route = $parsed_regexp['route'];
        $this->regexp = $parsed_regexp['regexp'];
        $this->params = $parsed_regexp['params'];

        $this->args = $args['args'];
        $this->permission_callback = $this->parseCallback($args['permission_callback'], false);

        return $this;
    }

    /**
     * @param Arrayable | array $parameters
     *
     * @return $this
     */
    public function useParameters($parameters)
    {
        // Sorted with raw params order
        // All missed parameters will be replaced with the original placeholder.
        $this->parameters = array_merge($this->paramsWithUrlPlaceholder(),
            array_map('urlencode', array_filter($this->getArraybleData($parameters))));

        return $this;
    }

    /**
     * @param Arrayable | array $parameters
     *
     * @return string
     */
    public function urlFromParams($parameters): string
    {
        return (string)str_replace($this->paramsWithUrlPlaceholder(),
            $this->useParameters($parameters)->parameters(), $this->uri());
    }

    /**
     * @return array
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function regexp(): array
    {
        return $this->regexp;
    }

    /**
     * @return string
     */
    public function route(): string
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function rewriteRule(): array
    {
        $matches = [];

        $matches_counter = 1;

        foreach ($this->params() as $param) {
            $matches[] = sprintf('%1$s=$matches[%2$d]', $param, $matches_counter++);
        }

        return [
            'matches' => implode('&', $matches),
            'regexp' => sprintf('^%s/?$', $this->route()),
        ];
    }

    /**
     * @return Closure
     */
    public function getCallback(): callable
    {
        return function() {
            return $this->resolver->resolve($this, func_get_args());
        };
    }

    /**
     * @return Closure|array
     */
    public function getHandler()
    {
        return $this->callback;
    }

    /**
     * @return Closure
     */
    public function getPermissionCallback(): callable
    {
        return function() {
            return $this->resolver->resolvePermission($this, func_get_args());
        };
    }

    /**
     * @return Closure|array|null
     */
    public function getPermissionHandler()
    {
        return $this->permission_callback;
    }

    /**
     * @param Resolver $resolver
     *
     * @return Route
     */
    protected function setResolver(Resolver $resolver): Route
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'args' => $this->args,
            'callback' => $this->getCallback(),
            'methods' => $this->methods(),
            'name' => $this->name(),
            'url' => $this->uri(),
            'params' => $this->params(),
            'params_raw' => $this->paramsWithUrlPlaceholder(),
            'permission_callback' => $this->getPermissionCallback(),
            'path' => $this->path(),
            'regexp' => $this->regexp(),
            'route' => $this->route(),
        ];
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        $arr = $this->toArray();

        // With Closure it can't be serialised
        unset($arr['callback'], $arr['permission_callback']);

        return serialize($arr);
    }

    /**
     * @param string $serialized
     * @param null $options
     *
     * @return mixed
     */
    public function unserialize($serialized, $options = null)
    {
        return unserialize($serialized, $options);
    }

    /**
     * Determines how the route should be presented as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->urlFromParams($this->parameters() ?: $this->paramsWithUrlPlaceholder());
    }

    /**
     * Parse callback
     *
     * @param callable|string $callback
     * @param bool $required            Determine if the parser should throw an exception
     *                                  if handler is invalid
     * @return array|Closure|string
     */
    protected function parseCallback($callback, $required = true)
    {
        if (is_callable($callback)) {
            $handler = $callback;
        } else if (is_string($callback) && strpos($callback, '@') !== false) {
            $handler = explode('@', $callback);
        } else if ($callback instanceof Closure) {
            $handler = $callback;
        } else {
            $handler = false;
        }

        if ($required && !$handler) {
            throw new InvalidArgumentException(sprintf('Invalid $callback: %s', print_r($callback, true)));
        }

        return $handler;
    }

    /**
     * Parse provided methods
     *
     * @param string|array $methods
     * @return array
     */
    protected function parseMethods($methods): array
    {
        return array_map('trim',
            array_map('strtoupper',
                !is_array($methods) ? explode(',', $methods) : (array) $methods
            )
        );
    }

    /**
     * Parse provided methods
     *
     * @param array $regexp      e.g. ['member_id' => '[0-9]{1,}']
     *
     * @return array
     */
    protected function parseRegexp(array $regexp): array
    {
        $parsed = [
            'params' => [],
            'regexp' => [],
            'route'  => $this->path(),
        ];

        $default_regexp = '.+?';

        // seeking params (e.g. {member_id}) in the path
        preg_match_all('/{(\w*)}/', $this->path(), $matches);

        // We found some params
        if (!empty($matches)) {
            // Map found params
            // [ 'member_id' => {member_id} ]
            $parsed['params'] = array_combine($matches[1], $matches[0]);

            // Could be if the regexp is not filled for all params
            if ($missed_params_in_regexp = array_diff_key($parsed['params'], $regexp)) {
                // Fill missed regexp with default one.
                $missed_params_in_regexp = array_fill_keys(array_keys($missed_params_in_regexp), $default_regexp);
                // Merge it back for further use.
                // [ 'member_id' => (.+?) ]
                $regexp = array_merge($regexp, $missed_params_in_regexp);
            }

            if ($this->use_named_regexp) {
                // Made named regexp
                foreach ($regexp as $param => $rxp) {
                    $named_param = "?P<{$param}>";

                    // check if either param or regexp already has a named option like (?P<member_id>[0-9]{1,})
                    // It might happen when REST in use.
                    $parsed['regexp'][$param] = strpos($param, $named_param) !== false || strpos($rxp, $named_param) !== false
                        ? $rxp // Using the original regexp
                        : sprintf('(?P<%s>%s)', $param, $rxp);
                }
            } else {
                $parsed['regexp'] = $regexp;
            }

            $parsed['route'] = str_replace($parsed['params'], $parsed['regexp'], $this->path());
        }

        return $parsed;
    }
}
