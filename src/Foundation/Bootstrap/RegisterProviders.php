<?php

namespace Rumur\Pimpled\Foundation\Bootstrap;

use Rumur\Pimpled\Foundation\Application;

class RegisterProviders
{
    /**
     * Bootstrap application service providers.
     *
     * @param Application $app
     */
    public function bootstrap(Application $app)
    {
        $app->registerConfiguredProviders();
    }
}
