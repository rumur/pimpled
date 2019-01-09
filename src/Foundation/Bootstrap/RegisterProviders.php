<?php

namespace Pmld\Foundation\Bootstrap;

use Pmld\Foundation\Application;

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
