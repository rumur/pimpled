<?php

namespace Rumur\Pimpled\Support\Facades;

/**
 * Class View
 *
 * @method static \Rumur\Pimpled\Contracts\View\View make(string $view, array $params = []): View
 * @method static \Rumur\Pimpled\Contracts\View\View first(array $views, $data = []): View
 * @method static string renderWhen($condition, $view, $data = []): string
 * @method static string renderWhenExists($view, $data = []): string
 * @method static \Rumur\Pimpled\Contracts\View\Factory share(mixed $params, $value = null)
 * @method static mixed shared($key, $default = null)
 * @method static array sharedData(): array
 * @method static bool exists($view): bool
 *
 * @see \Rumur\Pimpled\View\Factory
 */
class View extends Facade
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
        return 'view';
    }
}
