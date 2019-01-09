<?php

namespace Pmld\Foundation\Bootstrap;

use Pmld\Foundation\Application;
use Pmld\Support\Facades\Facade;

class RegisterFacades
{
    public function bootstrap(Application $app)
    {
        Facade::setFacadeApplication($app);
    }
}
