<?php

namespace Pmld\App\Migrations;

use Rumur\Pimpled\Support\AggregateServiceProvider;
use Rumur\Pimpled\Routing\Database\DeleteRoutesHash;

class Aggregator extends AggregateServiceProvider
{
    protected $providers = [
        DummyTable::class,
        DeleteRoutesHash::class,
    ];
}
