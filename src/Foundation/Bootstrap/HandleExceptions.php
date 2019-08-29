<?php

namespace Rumur\Pimpled\Foundation\Bootstrap;

use Rumur\Pimpled\Foundation\Application;

class HandleExceptions
{
    /**
     * The application instance.
     *
     * @var \Rumur\Pimpled\Foundation\Application
     */
    protected $app;

    /**
     * Bootstrap the given application.
     *
     * @param  \Rumur\Pimpled\Foundation\Application  $app
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

