<?php

namespace Rumur\Pimpled\Support\Facades;

/**
 * Class Mail
 *
 * @method static \Rumur\Pimpled\Contracts\View\View make(string $view, array $params = [])
 * @method static \Rumur\Pimpled\Contracts\View\Factory share(mixed $params, $value = null)
 */
class Mail extends Facade
{
    /**
     * Return the service provider key responsible for the request class.
     * The key must be the same as the one used when registering
     * the service provider.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mailer';
    }
}
