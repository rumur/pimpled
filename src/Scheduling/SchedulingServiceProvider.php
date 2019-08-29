<?php

namespace Rumur\Pimpled\Scheduling;

use Pimple\Container;
use Rumur\Pimpled\Support\ServiceProvider;
use Rumur\Pimpled\Contracts\Support\Arrayable;

class SchedulingServiceProvider extends ServiceProvider
{
    /** @inheritDoc */
    public function register(Container $app)
    {
        $this->registerRecurrence();
        $this->registerDispatcher($app['config']->get('scheduling.jobs', []));

        $app['schedule'] = $app->factory(static function($app) {
            return new Scheduler();
        });
    }

    /**
     * Registers available recurrences
     */
    protected function registerRecurrence()
    {
        Recurrence::register();
    }

    /**
     * Registers scheduled tasks.
     *
     * @param Arrayable | array $jobs
     */
    protected function registerDispatcher($jobs)
    {
        (new Dispatcher)->registerMultiple($jobs);
    }
}
