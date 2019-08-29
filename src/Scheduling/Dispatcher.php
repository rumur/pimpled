<?php

namespace Rumur\Pimpled\Scheduling;

use Closure;
use \Rumur\Pimpled\Contracts\Scheduling\Job as JobContract;
use Rumur\Pimpled\Contracts\Support\Arrayable;
use Rumur\Pimpled\Support\Collection;

class Dispatcher
{
    /**
     * @var Closure
     */
    protected $resolver;

    /**
     * Registers action to the system.
     *
     * @param $action
     *
     * @return Dispatcher
     */
    public function registerAction($action): Dispatcher
    {
        add_action($action, $this->getResolver(), 10, 2);

        return $this;
    }

    /**
     * @param JobContract $job
     *
     * @uses add_action()
     *
     * @return Dispatcher
     */
    public function register(JobContract $job): Dispatcher
    {
        return $this->registerAction($job->name());
    }

    /**
     * @param Arrayable | array $jobs
     */
    public function registerMultiple($jobs = [])
    {
        Collection::make($jobs)->map(function($job) {
            // Instantiate a Job
            if (is_string($job) && class_exists($job)) {
                $job = new $job;
            }
            return $job;
        })->each(function(JobContract $job) {
            $this->register($job);
        });
    }

    /**
     * @param JobContract $job
     *
     * @param array $args
     */
    public function dispatch(JobContract $job)
    {
        error_log(print_r(func_get_args()));
    }

    /**
     * @return callable
     */
    public function getResolver(): callable
    {
        return $this->resolver ?: $this;
    }

    /**
     * @param callable $resolver
     *
     * @return Dispatcher
     */
    public function useResolver(callable $resolver): Dispatcher
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * @param $job
     * @param $args
     * @throws \Throwable
     */
    public function __invoke($job, $args)
    {
        try {

            do_action('pmld.scheduling.before_dispatch', $job, $args);

            if (class_exists($job)) {
                call_user_func_array([new $job, 'handle'], $args);
            } elseif (is_callable($job)) {
                call_user_func_array($job, $args);
            }

            do_action('pmld.scheduling.after_dispatch', $job, $args);

        } catch (\Throwable $e) {
            do_action('pmld.scheduling.dispatched_failed', $job, $args, $e);
            do_action('pmld.scheduling.dispatched_failed_' . current_action(), $job, $args, $e);
            throw $e;
        }
    }
}
