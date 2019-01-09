<?php

namespace Pmld\Foundation\Bootstrap;

use Pmld\Foundation\Application;

class HandleExceptions
{
    /**
     * The application instance.
     *
     * @var \Pmld\Foundation\Application
     */
    protected $app;

    /**
     * Bootstrap the given application.
     *
     * @param  \Pmld\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        // Pretty errors.
        if (($app->isDevelopment() || $app->isLocal()) && WP_DEBUG && ! isset($_GET['no-debug'])) {
            $whoops = new \Whoops\Run();
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            $whoops->register();
        }
    }
}

