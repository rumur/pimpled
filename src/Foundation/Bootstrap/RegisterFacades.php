<?php

namespace Rumur\Pimpled\Foundation\Bootstrap;

use Rumur\Pimpled\Foundation\Application;
use Rumur\Pimpled\Support\Facades\Facade;

class RegisterFacades
{
    public function bootstrap(Application $app)
    {
        Facade::setFacadeApplication($app);
    }
}
