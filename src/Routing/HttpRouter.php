<?php

namespace Rumur\Pimpled\Routing;

use Closure;
use InvalidArgumentException;
use Rumur\Pimpled\Http\Redirector;
use Rumur\Pimpled\Contracts\View\View;

/**
 * Class HttpRouter
 *
 * @author rumur
 */
class HttpRouter extends Router
{
    /**
     * Adds the support of the multi language
     *
     * @var array
     */
    protected $multi_lang = [];

    /** @var Closure */
    protected $dispatcher;

    /** @inheritDoc */
    protected function boot()
    {
        parent::boot();

        $this->registerNamespace();
        $this->registerDispatcher();
    }

    /**
     * Adds support of the PolyLang plugin.
     * Helps register routes for all languages.
     *
     * @link https://wordpress.org/plugins/polylang/
     *
     * @return $this
     */
    public function usePolyLang()
    {
        if (function_exists('pll_the_languages')
            && ($languages = pll_the_languages(['raw' => 1]))
            && is_array($languages)) {

            $this->multi_lang = [
                'languages' => array_keys($languages),
                'current' => pll_current_language(),
                'default' => $default = pll_default_language(),
                'list' => array_diff(array_keys($languages), [$default])
            ];
        }

        return $this;
    }

    /**
     * Adds support of the WPML plugin.
     * Helps register routes for all languages.
     *
     * @link https://wpml.org/
     *
     * @return $this
     */
    public function useWPML()
    {
        if (function_exists('wpml_get_current_language')) {

            $args = [
                'skip_missing' => 0,
                'orderby' => 'custom',
                'order' => 'asc',
            ];

            $languages = wpml_get_active_languages_filter(null, $args);

            $this->multi_lang = [
                'languages' => array_keys($languages),
                'current' => wpml_get_current_language(),
                'default' => $default = wpml_get_default_language(),
                'list' => array_diff(array_keys($languages), [$default])
            ];
        }

        return $this;
    }

    /**
     * Registers a custom dispatcher
     *
     * @param string|Closure|callable $dispatcher
     *
     * @return $this
     */
    public function useDispatcher($dispatcher)
    {
        if (!$this->dispatcher) {
            $this->dispatcher = $this->validateDispatcher($dispatcher);
        }

        return $this;
    }

    /**
     * Checks whether it's a multi lang setup
     *
     * @return bool
     */
    protected function isMLSetup(): bool
    {
        return !empty($this->multi_lang);
    }

    /**
     * @param string $route
     * @param array $args
     *
     * @return $this
     *
     * @author rumur
     */
    public function addRoute($route, array $args)
    {
        // Adds the current route
        parent::addRoute($route, $args);

        // Register this route
        // for all available languages
        if ($this->isMLSetup()) {

            foreach ($this->multi_lang['list'] as $lang_code) {

                $args_with_lang_code = $args;

                // if you have e.g. route with path "profile" you'll get a "de/profile"
                $route_with_lang_code = sprintf('%s/%s', $lang_code, ltrim($route, '/'));

                if (isset($args['name'])) {
                    $args_with_lang_code['name'] = "{$args['name']}.{$lang_code}"; // profile -> profile.de
                }

                parent::addRoute($route_with_lang_code, $args_with_lang_code);
            }
        }

        return $this;
    }

    /**
     * Prefix for variables that will occur within the $wp_query.
     *
     * @return string
     */
    public function prefix(): string
    {
        return trim(parent::prefix(), '_') . '_';
    }

    /**
     * Registers Namespace
     */
    protected function registerNamespace()
    {
        add_rewrite_tag('%is_' . $this->prefix() . '_route%', '(\w+)');
    }

    /**
     * Resolver of the route content.
     */
    protected function registerDispatcher()
    {
        if ($this->dispatcher) {
            call_user_func($this->dispatcher, $this);
        } else {

            add_filter('request', function (array $query_vars) {

                if ($route_id = $query_vars["is_{$this->prefix()}_route"] ?? false) {

                    $method = $_SERVER['REQUEST_METHOD'] ?? static::READABLE;

                    if ($route = $this->repository()->get($route_id, $method)) {

                        // Replacing the default
                        add_action('template_redirect', function() use ($route) {

                            $params = array_map('get_query_var', $route->params());

                            /**
                             * @param mixed $response
                             * @param array $params
                             * @param Route $route
                             * @param Router $router
                             */
                            $response = apply_filters('pmld.routing.dispatcher_response',
                                call_user_func_array($route->getCallback(), $params), $params, $route, $this);

                            if ($response instanceof View) {
                                echo $response; die;
                            }

                            if ($response instanceof Redirector) {
                                $response->redirect();
                            }

                            /**
                             * @param mixed $response
                             * @param Http_Router $this
                             */
                            do_action('pmld.routing.dispatch_response', $response, $this);
                        });

                    } else {
                        // We didn't find the route, so set 404 as true
                        set_query_var('is_404', true);
                    }
                }

                return $query_vars;
            });
        }
    }

    /**
     * @inheritDoc
     * @param Route $namespace
     *
     * @link https://codex.wordpress.org/Rewrite_API/add_rewrite_rule
     *
     * @param string $position
     */
    protected function registerRoute(Route $route, $position = 'top')
    {
        $rewrite_rule = $route->rewriteRule();

        $query = rtrim(sprintf('index.php?is_%s_route=%s&%s',
            $this->prefix(), $route->id(), $rewrite_rule['matches']
        ), '&');

        add_rewrite_rule($rewrite_rule['regexp'], $query, $position);
        //add_rewrite_rule('^'.$namespace.'/(.*)?', $query, 'top');
        // (?:\/(?:.*)?|\/?$) // The end of regexp for the whole route

        $params = $route->params();
        $regexp = $route->regexp();

        foreach ($params as $param) {
            $regex = !is_array($regexp) ? $regexp : $regexp[$param] ?? $regexp;
            add_rewrite_tag("%{$param}%", $regex);
        }
    }

    /**
     * Parse Dispatcher
     *
     * @param mixed $dispatcher
     * @return array|Closure|string
     */
    protected function validateDispatcher($dispatcher)
    {
        if (is_callable($dispatcher)) {
            $handler = $dispatcher;
        } else if (is_string($dispatcher) && strpos($dispatcher, '@') !== false) {
            list($dispatcher, $action) = explode('@', $dispatcher);
            $handler = [new $dispatcher, $action];
        } else if ($dispatcher instanceof Closure) {
            $handler = $dispatcher;
        } else {
            $handler = false;
        }

        if (!$handler) {
            throw new InvalidArgumentException(sprintf('Invalid Dispatcher: %s', print_r($dispatcher, true)));
        }

        return $handler;
    }
}
