<?php

namespace Rumur\Pimpled\Routing;

use Pimple\Container;
use Rumur\Pimpled\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     * @param Container $app
     */
    public function register(Container $app)
    {
        $this->mapWebRoutes();
        $this->mapApiRoutes();

        $app['web.route'] = static function ($app) {
           return HttpRouter::make('pmld');
        };

        $app['api.route'] = static function ($app) {
            return ApiRouter::make('pmld');
        };

        $app['url'] = static function ($app) {
            return (new UrlGenerator($app))->setRoutes(array_merge(
                $app['web.route']->repository()->withNameOnly(),
                $app['api.route']->repository()->withNameOnly()
            ));
        };
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        if (file_exists($web = $this->app->basePath('routes/web.php'))) {
            add_action('init', function() use ($web) {
                include_once $web;

                $routes = $this->app['web.route']
                    ->registerRoutes()->getRoutes();

                $routes_hash = md5(serialize($routes));

                $routes_hash_saved = get_option('pmld_routes_hash');

                if ($routes_hash !== $routes_hash_saved) {
                    flush_rewrite_rules();
                    update_option('pmld_routes_hash', $routes_hash);
                }
            });
        }
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        if (file_exists($api = $this->app->basePath('routes/api.php'))) {
            add_action('rest_api_init', function() use ($api) {
                include_once $api;
                $this->app['api.route']->registerRoutes();
            });
        }
    }
}
