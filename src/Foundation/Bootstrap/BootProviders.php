<?php

namespace Rumur\Pimpled\Foundation\Bootstrap;

use Rumur\Pimpled\Foundation\Application;

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
