<?php

namespace Pmld\Foundation\Http;

use Pimple\Container;
use Pmld\Support\ServiceProvider;

class ResponseService extends ServiceProvider
{
    /**
     * @param Container $app
     */
    public function register(Container $app)
    {
        $app['response'] = function () {
          return new Response();
        };
    }
}
