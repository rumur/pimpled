<?php

namespace Rumur\Pimpled\Scheduling;

use Rumur\Pimpled\Contracts\Support\Arrayable;
use Rumur\Pimpled\Contracts\Scheduling\Job as JobContract;

abstract class Job implements JobContract, Arrayable
{
    /**
     * The set of args that will be passed to a handler
     *
     * @var array
     */
    protected $args = [];

    /**
     * Scheduled_Task constructor.
     *
     * @param Arrayable | array $args
     */
    public function __construct($args = [])
    {
        $this->useArgs($args);

        $this->boot();
    }

    /**
     * Boot the task
     */
    protected function boot()
    {
      // Do some setups for a Task
    }

    /**
     * @return array|null
     */
    public function args()
    {
        return $this->args;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return sanitize_title(str_replace(['\\'], '_', static::class));
    }

    /**
     * @param Arrayable | array $args
     * @return $this
     */
    public function useArgs($args = [])
    {
        // In order to pass all args to a handler
        // we need to wrap with the array.
        $this->args = $this->parseArgs($args);

        return $this;
    }

    /**
     * @param Arrayable | array $args
     *
     * @return array
     */
    protected function parseArgs($args = null): array
    {
        if ($args instanceof Arrayable) {
            $args = $args->toArray();
        }

        return ['job' => static::class, 'args' => $args];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->args;
    }
}
