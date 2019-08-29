<?php

namespace Rumur\Pimpled\Support;

use Pimple\Container;

class AggregateServiceProvider extends ServiceProvider
{
    /** @var Container */
    protected $app;

    /**
     * The provider class names.
     *
     * @var array
     */
    protected $providers = [];

    /**
     * An array of the service provider instances.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Register the service provider.
     *
     * @param Container $app
     *
     * @return void
     */
    public function register(Container $app)
    {
        $this->app = $app;

        $this->instances = array_map(function($provider) {
            return $this->app->register(new $provider);
        }, $this->providers);
    }
}
