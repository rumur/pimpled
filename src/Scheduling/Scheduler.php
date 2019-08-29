<?php

namespace Rumur\Pimpled\Scheduling;

use InvalidArgumentException;
use Rumur\Pimpled\Contracts\Scheduling\Job as JobContract;

/**
 * Class Schedule
 *
 * @mixin HasSingleRecurrence
 * @mixin HasMultipleRecurrence
 *
 * @package Rumur\Pimpled\Scheduling
 */
class Scheduler
{
    use CanResign,
        HasSingleRecurrence,
        HasMultipleRecurrence,
        HasCalculatedRecurrence;

    /** @var Job $task */
    protected $task;

    /**
     * @var string
     *
     * @link https://www.php.net/manual/en/function.date.php
     */
    protected $time_format = 'H:i';

    /**
     * @param JobContract $task
     * @return $this
     */
    public function job(JobContract $task)
    {
        $this->resolveTask($task);

        return $this;
    }

    /**
     * @param JobContract|null $task
     *
     * @return JobContract
     * @throws InvalidArgumentException
     */
    protected function resolveTask(JobContract $task = null): JobContract
    {
        if ($task) {
            $this->task = apply_filters('pmld.scheduling.resolving_task', $task);
        }

        if (!$this->task) {
            throw new InvalidArgumentException(sprintf('%s did not get any task to resolve', __METHOD__));
        }

        return $this->task;
    }

    /**
     * @param JobContract $task
     * @return false|int
     */
    public function isJobRegistered(JobContract $task = null)
    {
        $task = $this->resolveTask($task);

        return wp_next_scheduled($task->name(), $task->args());
    }

    /**
     * @param JobContract $task
     * @return false|int
     */
    public function isJobNotRegistered(JobContract $task = null)
    {
        return !$this->isJobRegistered($task);
    }

    /**
     * Gets the Now time in timestamp
     *
     * @return int
     */
    protected function now(): int
    {
        return Recurrence::now();
    }

    /**
     * @param $recurrent
     * @return bool
     */
    protected function has($recurrent): bool
    {
        return Recurrence::has($recurrent);
    }

    /**
     * @param string $time
     * @return string
     */
    protected function parseTime($time): string
    {
        return $this->parseFormat($this->time_format, $time);
    }

    /**
     * @param string $format
     * @param string $value
     * @return string
     */
    protected function parseFormat($format, $value)
    {
        if (!($parsed = date($format, strtotime($value)))) {
            throw new InvalidArgumentException(sprintf('%s must be a valid %s format', printf($value, true), $format));
        }

        return $parsed;
    }
}
