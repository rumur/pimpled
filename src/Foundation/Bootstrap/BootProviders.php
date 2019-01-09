<?php

namespace Pmld\Foundation\Bootstrap;

use Pmld\Foundation\Application;

class BootProviders
{
    /**
     * Bootstrap the application.
     *
     * @param Application $app
     */
    public function bootstrap(Application $app)
    {
        $app->boot();
    }
}
