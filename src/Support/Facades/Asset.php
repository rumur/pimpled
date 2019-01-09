<?php

namespace Pmld\Support\Facades;

/**
 * Class Asset
 *
 * @method static string get( $asset )
 * @method static string getKey( $asset )
 *
 * @see Pmld\Foundation\Asset\Asset
 */
class Asset extends Facade
{
    /** @inheritdoc */
    protected static function getFacadeAccessor()
    {
        return 'asset';
    }
}
