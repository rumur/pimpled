<?php

namespace Rumur\Pimpled\View;

use Pimple\Container;
use Rumur\Pimpled\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Registers view engine.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $container['view'] = static function ($app) {
            return (new Factory($app))->share('app', $app)
                ->useDirWithinThemes($app->pluginData('name'));
        };
    }
}
