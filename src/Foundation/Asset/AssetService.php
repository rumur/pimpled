<?php

namespace Pmld\Foundation\Asset;

use Pimple\Container;
use Pmld\Support\ServiceProvider;

class AssetService extends ServiceProvider
{
    /**
     * @param Container $app
     *
     * @since v1.0.0
     */
    public function register(Container $app)
    {
        $app['asset'] = function($app) {
            /**
             * @var \Pmld\Foundation\Application $app
             */
            $dist_uri = $app->getPublicUrl('dist');
            $manifest_path = $app->basePath('dist/mix-manifest.json');

            return new Asset($manifest_path, $dist_uri);
        };
    }
}
