<?php

namespace Pmld\App\Assets;

use Pmld\Support\AggregateServiceProvider;

class Aggregator extends AggregateServiceProvider
{
    protected $providers = [
        AssetsCommon::class,
        AssetsFront::class,
        AssetsAdmin::class,
    ];
}
