<?php

namespace Rumur\Pimpled\Scheduling;

use Rumur\Pimpled\Contracts\Scheduling\Job as JobContract;

trait HasMultipleRecurrence
{
    /**
     * Run the task every minute
     *
     * @return $this
     */
    public function everyMinute()
    {
        return $this->registerRecurrence('every-minute', $this->resolveTask());
    }

    /**
     * Run the task every five minutes
     *
     * @return $this
     */
    public function everyFiveMinutes()
    {
        return $this->registerRecurrence('every-five-minutes', $this->resolveTask());
    }

    /**
     * Run the task every ten minutes
     *
     * @return $this
     */
    public function everyTenMinutes()
    {
        return $this->registerRecurrence('every-ten-minutes', $this->resolveTask());
    }

    /**
     * Run the task every fifteen minutes
     *
     * @return $this
     */
    public function everyFifteenMinutes()
    {
        return $this->registerRecurrence('every-fifteen-minutes', $this->resolveTask());
    }

    /**
     * Run the task every thirty minutes
     *
     * @return $this
     */
    public function everyThirtyMinutes()
    {
        return $this->registerRecurrence('every-thirty-minutes', $this->resolveTask());
    }

    /**
     * Run the task every hour
     *
     * @return $this
     */
    public function hourly()
    {
        return $this->registerRecurrence('hourly', $this->resolveTask());
    }

    /**
     * Run the task every day at midnight
     *
     * @return $this
     */
    public function daily()
    {
        return $this->registerRecurrence('daily', $this->resolveTask());
    }

    /**
     * Run the task every week
     */
    public function weekly()
    {
        return $this->registerRecurrence('weekly', $this->resolveTask());
    }

    /**
     * Run the task every month
     */
    public function monthly()
    {
        return $this->registerRecurrence('monthly', $this->resolveTask());
    }

    /**
     * Run the task every quarter
     *
     * @return $this
     */
    public function quarterly()
    {
        return $this->registerRecurrence('quarterly', $this->resolveTask());
    }

    /**
     * Run the task every year
     *
     * @return $this
     */
    public function yearly()
    {
        return $this->registerRecurrence('yearly', $this->resolveTask());
    }

    /**
     * Adds a recurrent Task to a system queue.
     *
     * @param string $recurrent    Available recurrence name
     * @param JobContract $task    The task to perform
     * @param int $timestamp       The calculated $timestamp
     *
     * @return $this
     *
     * @uses wp_schedule_event()
     */
    protected function registerRecurrence($recurrent, JobContract $task, $timestamp = null)
    {
        if ($this->has($recurrent) && $this->isJobNotRegistered($task)) {
            wp_schedule_event($timestamp ?: $this->now(), $recurrent, $task->name(), $task->args());
        }

        return $this;
    }
}
