<?php

namespace Rumur\Pimpled\Routing;

use Closure;
use WP_Error;
use Throwable;
use Requests_Exception_HTTP;
use Requests_Exception_HTTP_401;
use Rumur\Pimpled\Contracts\View\View;
use function Rumur\Pimpled\Support\{app, view};

/**
 * Class Resolver
 *
 * @package Routing
 *
 * @author rumur
 */
class Resolver
{
    /** @var bool */
    protected $is_rest_request;

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return app()->isDebug() ?? defined('WP_DEBUG') && WP_DEBUG;
    }

    /**
     * @param Route $route
     * @param array $args
     *
     * @return View|mixed
     */
    public function resolve(Route $route, array $args)
    {
        try {

            if ($this->is_rest_request = (!empty($args) && $args[0] instanceof \WP_REST_Request)) {
                return $this->resolveREST($route, $args);
            }

            $is_allowed = $this->resolvePermission($route, $args);

            if ($is_allowed && !is_wp_error($is_allowed)) {
                // If we passed a permission we echoing the result and die
                return call_user_func_array($this->makeHandler($route->getHandler()), $args);
            }

            // Picking the right Exception from the available ones.
            if (is_wp_error($is_allowed)) {
                $error_class = Requests_Exception_HTTP::get_class($is_allowed->get_error_code());
                throw new $error_class($is_allowed->get_error_message() ?: null, $is_allowed->get_error_data() ?: null);
            }

            throw new Requests_Exception_HTTP_401();

        } catch (Throwable $e) {
            error_log($e->getMessage());
            return $this->handleException($e);
        }
    }

    /**
     * @param Route $route
     * @param array $args
     *
     * @return bool|WP_Error
     */
    public function resolvePermission(Route $route, array $args)
    {
        if ($handler = $route->getPermissionHandler()) {
            // TODO implement a proper middleware here.
            return call_user_func_array($this->makeHandler($handler), $args);
        }

        return true;
    }

    /**
     * @param Route $route
     * @param $args
     *
     * @return mixed|\WP_REST_Response
     */
    public function resolveREST(Route $route, $args)
    {
        try {
            /** @var \WP_REST_Request $request */
            $request = $args[0];

            // Fixing params presents in the action
            $args = array_map(static function ($param) use ($request) {
                return $request->get_param($param);
            }, $route->params());

            // adding the WP_REST_Request to the end.
            $args['__request'] = $request;

            return rest_ensure_response(call_user_func_array($this->makeHandler($route->getHandler()), $args));

        } catch (Requests_Exception_HTTP $e) {
            return rest_ensure_response(new WP_Error($e->getCode(), $e->getReason(), $e->getData()));
        } catch (Throwable $e) {
            return rest_ensure_response(new WP_Error($e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @param Closure|array
     *
     * @return array|Closure
     */
    protected function makeHandler($handler)
    {
        // If it's an array we need to make an instance
        if (is_array($handler) && class_exists($handler[0])) {
            $handler[0] = new $handler[0];
        }

        return $handler;
    }

    /**
     * @param Throwable $e
     *
     * @return \Rumur\Pimpled\Contracts\View\Factory|\Rumur\Pimpled\Contracts\View\View
     */
    protected function handleException(Throwable $e)
    {
        $data = ['code' => 500, 'message' => __('Unknown Error Occur'), '__e' => $e];

        $data = array_merge($data, ['code' => $e->getCode(), 'message' => $e->getMessage()]);

        if ($e instanceof Requests_Exception_HTTP) {
            $data = array_merge($data, ['message' => $e->getReason()]);
        }

        return view()->first([
            'exceptions',
            'common.exceptions',
        ], $data);
    }
}
