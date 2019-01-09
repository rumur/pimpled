<?php

namespace Pmld\Foundation\Http;

use Pmld\Support\Facades\App;
use Pmld\Support\Facades\Request;
use Pmld\Support\Facades\Response;
use Pmld\Contracts\Http\Request as RequestContract;
use Pmld\Contracts\Http\Api\Middleware as MiddlewareContract;

/**
 * Class Route
 *
 * @method static Route|\WP_Error any(string $namespace, string $regexp, array $args)
 * @method static Route|\WP_Error put(string $namespace, string $regexp, array $args)
 * @method static Route|\WP_Error patch(string $namespace, string $regexp, array $args)
 * @method static Route|\WP_Error post(string $namespace, string $regexp, array $args)
 * @method static Route|\WP_Error creatable(string $namespace, string $regexp, array $args)
 * @method static Route|\WP_Error get(string $namespace, string $regexp, array $args)
 * @method static Route|\WP_Error readable(string $namespace, string $regexp, array $args)
 * @method static Route|\WP_Error delete(string $namespace, string $regexp, array $args)
 */
class Route {
    /** @var string */
    const ACTION_DELIMITER = '@';

    /** @var bool  */
    protected static $is_group = false;
    /** @var array  */
    protected static $group_args = [];

    /**
     * Make a route for several methods.
     *
     * @param array  $methods
     * @param        $namespace
     * @param string $regexp
     * @param array  $args
     *
     * @return array
     *
     * @author rumur
     */
    static public function match( array $methods, $namespace, $regexp = '', array $args = [] )
    {
        return array_map( function ( $method ) use ( $namespace, $regexp, $args ) {
            return static::$method( $namespace, $regexp, $args );
        }, $methods );
    }

    /**
     * Makes a group of routes.
     *
     * @param string            $namespace
     * @param array | string    $middleware
     * @param \Closure          $group_call
     *
     * @author rumur
     */
    static public function group($namespace, $middleware, \Closure $group_call)
    {
        static::$group_args = [ 'middleware' => $middleware ];
        static::$is_group = true;

        call_user_func( $group_call, $namespace );

        static::$group_args = [];
        static::$is_group = false;
    }

    /**
     * The Fallback method.
     *
     * @param $name
     * @param $arguments
     *
     * @uses _doing_it_wrong()
     *
     * @return Route|\WP_Error
     *
     * @author rumur
     */
    public static function __callStatic( $name, $arguments )
    {
        switch ( $name ) {
            case 'get':
            case 'readable':
                $methods = Server::READABLE;
                break;
            case 'post':
            case 'creatable':
                $methods = Server::CREATABLE;
                break;
            case 'delete':
            case 'deletable':
                $methods = Server::DELETABLE;
                break;
            case 'put':
                $methods = 'PUT'; // @TODO maybe use instead -> \WP_REST_Server::EDITABLE,
                break;
            case 'patch':
                $methods = 'PATCH'; // @TODO maybe use instead -> \WP_REST_Server::EDITABLE,
                break;
            case 'any':
                $methods = Server::ALLMETHODS;
                break;
            default:
                \_doing_it_wrong(
                    __CLASS__ . '::' . $name,
                    __( 'The wrong method provided.', PMLD_TD ),
                    null
                );

                return null;
        }

        $self = new static();

        /**
         * @var string $namespace
         * @var string $regexp
         * @var string|array $args
         */
        list($namespace, $regexp, $args) = $arguments;

        // Controller could be as a string.
        if ( is_string( $args ) ) {
            $args = [
                'uses' => $args,
            ];
        }

        $self->mergeGroup($args);

        $args = $self->parseArgs( [
            'methods' => $methods,
        ], $args );

        $self->register( $namespace, $regexp, $args );

        return $self;
    }

    /**
     * Makes the route available for the REST API
     *
     * @param string $namespace     Route Namespace e.g. `todo\v1`
     * @param string $regexp        Regexp
     * @param array  $args          Compatible with \WP_REST_Server::register_route
     *
     * @uses register_rest_route()
     *
     * @return boolean
     *
     * @author rumur
     */
    protected function register( $namespace, $regexp = '', array $args = [] )
    {
        return \register_rest_route( $namespace, $regexp, $args );
    }

    /**
     * Merge Groups Middleware.
     *
     * @param $args
     *
     * @author rumur
     */
    protected function mergeGroup(&$args)
    {
        // Dealing with group of routes.
        if ( static::$is_group && ! empty( static::$group_args ) ) {
            // Combine groups args with Route own args.
            $args = array_merge_recursive( static::$group_args, $args );

            // Make sure for passing a correct middleware.
            if ( isset( $args['middleware'] ) && is_array( $args['middleware'] ) ) {
                // User might been playing with simple middleware.
                $has_closure_as_middleware = array_filter( $args['middleware'], function($middleware) {
                    return $middleware instanceof \Closure;
                } );

                $args['middleware'] = array_filter( $args['middleware'] );

                // Make Then be a unique.
                if (count($has_closure_as_middleware) == 0) {
                    $args['middleware'] = array_unique($args['middleware']);
                }
            }
        }
    }

    /**
     * Makes args eatable for `register_rest_route` function.
     *
     * @param array $args
     * @param array $coming_args
     *
     * @uses wp_parse_args()
     *
     * @return array
     *
     * @author rumur
     */
    protected function parseArgs( array $args, array $coming_args )
    {
        $args = \wp_parse_args( $args, $coming_args );

        try {
            // Making back compat.
            if ( isset( $args['uses'] ) && ! isset( $args['callback'] ) ) {
                $args['callback'] = $args['uses'];

                unset( $args['uses'] );
            }

            if ( isset( $args['middleware'] ) ) {
                $this->injectMiddleware( $args );
            }

            $delimiter = static::ACTION_DELIMITER;

            // We're assuming that the route has a Controller@action callback.
            $has_class_method = strpos($args['callback'], $delimiter) !== false;

            if ( $has_class_method ) {

                $controller_action = explode($delimiter, $args['callback']);

                $controller = $controller_action[0];
                $action = $controller_action[1];

                $args['callback'] = function ($request) use ($controller, $action) {

                    if ($request instanceof RequestContract) {
                        $params = $request->all();
                    } else if ($request instanceof \WP_REST_Request) {
                        $params = array_merge(
                            $request->get_default_params(),
                            $request->get_url_params(),
                            $request->get_body_params()
                        );
                    } else {
                        $params = func_get_args();
                    }

                    /** @var \Pmld\Contracts\Http\Api\Response $response */
                    $response = Response::getInstance();

                    try {
                        return call_user_func_array([new $controller($request, $response), $action], $params);
                    } catch (\Requests_Exception_HTTP $e) {

                        return $response->dispatchWith(array_merge([
                            'message' => $e->getReason(),
                        ], (array) $e->getData()), $e->getCode());

                    } catch (\Throwable $e) {
                        error_log($e);
                        $message = $e->getMessage();
                    }

                    $error_params = compact('params', 'message');

                    do_action('pmld.rest_request_error', $error_params);

                    return $response->serverError([
                        'message' => App::isDebug()
                            ? $error_params
                            : __('Something went wrong', PMLD_TD)
                    ]);
                };
            }
        } catch ( \Exception $e ) {
            //new \WP_Error( 'route_wrong_args_structure', $e->getMessage() );
        }

        return $args;
    }

    /**
     * Injects the Middleware for permission check.
     *
     * @see http://v2.wp-api.org/extending/adding/#permissions-callback
     *
     * @param $args
     *
     * @uses \is_wp_error()
     *
     * @author rumur
     */
    protected function injectMiddleware( &$args )
    {
        if ( ! isset( $args['permission_callback'] ) ) {
            $middleware = $args['middleware'];

            unset( $args['middleware'] );

            $args['permission_callback'] = function () use ( $middleware ) {
                $validate_middleware = function ( $middleware, $args ) {
                    // User might be playing around with middleware.
                    if ( $middleware instanceof \Closure ) {
                        $func = $middleware;
                    } else {
                        $middleware = new $middleware();

                        $func = $middleware instanceof MiddlewareContract
                            ? [ $middleware, 'handle' ]
                            : false;
                    }

                    return $func
                        ? call_user_func_array( $func, $args )
                        : new \WP_Error( 'route_doing_wrong', __( 'Wrong Middleware has been provided.', PMLD_TD ) );
                };

                /**
                 * Gather all arguments together for `permission_callback` function.
                 * Args are passed from @see `wp-includes/rest-api.php:rest_send_allow_header`
                 */
                $callback_args = array_merge([
                    Request::getInstance(),
                ], func_get_args());

                //$callback_args = func_get_args();

                if ( is_array( $middleware ) ) {
                    foreach ( $middleware as $guard ) {
                        $middleware_result = $validate_middleware( $guard, $callback_args );
                        // if we got an error we don't have to check others.
                        if ( false === $middleware_result || \is_wp_error( $middleware_result ) ) {
                            return $middleware_result;
                        }
                    }
                    // If we get to that point, it means that we didn't face any problem.
                    return true;
                } else {
                    return $validate_middleware( $middleware, $callback_args );
                }
            };
        }
    }
}
