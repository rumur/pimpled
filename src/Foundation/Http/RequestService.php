<?php

namespace Pmld\Foundation\Http;

use Pimple\Container;
use Pmld\Support\ServiceProvider;

class RequestService extends ServiceProvider
{
    /**
     * @param Container $app
     */
    public function register(Container $app)
    {
        $app['request'] = function () {
          return Request::make();
        };
    }
}
