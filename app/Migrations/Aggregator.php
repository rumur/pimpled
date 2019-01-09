<?php

namespace Pmld\App\Migrations;

use Pmld\Support\AggregateServiceProvider;

class Aggregator extends AggregateServiceProvider
{
    protected $providers = [
        DummyTable::class,
    ];
}
