<?php

namespace Rumur\Pimpled\Mail;

use Pimple\Container;
use Rumur\Pimpled\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Registers view engine.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $container['mailer'] = static function ($app) {

        };
    }
}
