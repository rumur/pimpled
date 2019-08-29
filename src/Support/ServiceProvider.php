<?php

namespace Rumur\Pimpled\Support;

use Rumur\Pimpled\Foundation\Application;
use Rumur\Pimpled\Contracts\ServiceProviderInterface;

abstract class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * ServiceProvider constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Boot the service provider
     */
    public function boot()
    {

    }
}
