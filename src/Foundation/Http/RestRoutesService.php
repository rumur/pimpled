<?php

namespace Pmld\Providers;

use Pmld\Plugin;
use Pimple\Container;
use Pmld\Foundation\Application;
use Pmld\Support\Facades\Config;
use Pmld\Support\ServiceProvider;
use Pmld\Support\Facades\NoticeAdmin;

class RestRoutesService extends ServiceProvider
{
    /** @var Application app */
    protected $app;

    /**
     * Registers the Config.
     *
     * @param Container $app
     *
     * @uses add_action()
     *
     * @return mixed|void
     *
     * @author rumur
     */
    public function register(Container $app)
    {
        /** @var Application app */
        $this->app = $app;

        /**
         * Registers all available routes.
         *
         * @since v1.0.0
         */
        \add_action('rest_api_init', [$this, 'registerRoutes']);
        \add_action('rest_api_init', [$this, 'registerCORS'], 15);
    }

    /**
     * Register Api Routes.
     *
     * @uses apply_filters()
     *
     * @author rumur
     */
    public function registerRoutes()
    {
        $routes = \apply_filters( 'pmld.api_routes_file_path',
            $this->app->basePath('routes' . DIRECTORY_SEPARATOR . 'api.php')
        );

        if (file_exists($routes)) {
            require_once $routes;
        } else {
            NoticeAdmin::error(sprintf(
                __('<b>%1$s</b> The Api Routes Not found <b>%2$s</b>.', PMLD_TD),
                Plugin::NAME, $routes
            ));
        }
    }

    /**
     * Allows receive the Api Request from specific host only.
     *
     * @author rumur
     */
    public function registerCORS()
    {
        if ($origin = Config::get('app.cors.origin', false)) {

            \remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

            \add_filter('rest_pre_serve_request', function ($value) use ($origin) {
                header( 'Access-Control-Allow-Origin:' . join(', ', $origin) );
                header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
                header( 'Access-Control-Allow-Credentials: true' );
                return $value;
            });
        }
    }
}
