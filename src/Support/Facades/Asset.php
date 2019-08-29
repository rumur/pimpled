<?php

namespace Rumur\Pimpled\Support\Facades;

/**
 * Class Asset
 *
 * @method static string get( $asset )
 * @method static string getKey( $asset )
 *
 * @see Rumur\Pimpled\Foundation\Asset\Asset
 */
class Asset extends Facade
{
    /** @inheritdoc */
    protected static function getFacadeAccessor()
    {
        return 'asset';
    }
}
