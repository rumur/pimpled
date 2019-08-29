<?php

namespace Rumur\Pimpled\Scheduling;

trait HasSingleRecurrence
{
    /**
     * Run Task one time in minutes
     *
     * @param int $min How many minutes before performing a task
     * @return $this
     */
    public function onceInMinutes($min)
    {
        return $this->registerOnce('minute', $this->resolveTask(), $min);
    }

    /**
     * Run Task one time in hours
     *
     * @param int $hours How many hours before performing a task
     * @return $this
     */
    public function onceInHours($hours)
    {
        return $this->registerOnce('hour', $this->resolveTask(), $hours);
    }

    /**
     * Run Task one time in days
     *
     * @param int $days How many days before performing a task
     *
     * @return $this
     */
    public function onceInDays($days)
    {
        return $this->registerOnce('day', $this->resolveTask(), $days);
    }

    /**
     * Run Task one time in weeks
     *
     * @param int $weeks How many weeks before performing a task
     *
     * @return $this
     */
    public function onceInWeeks($weeks)
    {
        return $this->registerOnce('week', $this->resolveTask(), $weeks);
    }

    /**
     * Run Task one time in months
     *
     * @param int $months How many months before performing a task
     *
     * @return $this
     */
    public function onceInMonths($months)
    {
        return $this->registerOnce('month', $this->resolveTask(), $months);
    }

    /**
     * Run Task one time in quarters
     *
     * @param int $quarters How many quarters before performing a task
     *
     * @return $this
     */
    public function onceInQuarters($quarters)
    {
        return $this->registerOnce('quarter', $this->resolveTask(), $quarters);
    }

    /**
     * Run Task one time in a year
     *
     * @return $this
     */
    public function onceInYear()
    {
        return $this->registerOnce('year', $this->resolveTask());
    }

    /**
     * Run Task one time in a minute
     *
     * @return $this
     */
    public function onceInMinute()
    {
        return $this->onceInMinutes(1);
    }

    /**
     * Run Task one time in five minutes
     *
     * @return $this
     */
    public function onceInFiveMinutes()
    {
        return $this->onceInMinutes(5);
    }

    /**
     * Run Task one time in ten minutes
     *
     * @return $this
     */
    public function onceInTenMinutes()
    {
        return $this->onceInMinutes(10);
    }

    /**
     * Run Task one time in 15 minutes
     *
     * @return $this
     */
    public function onceInFifteenMinutes()
    {
        return $this->onceInMinutes(15);
    }

    /**
     * Run Task one time in 30 minutes
     *
     * @return $this
     */
    public function onceInThirtyMinutes()
    {
        return $this->onceInMinutes(30);
    }

    /**
     * Run Task one time in an hour
     *
     * @return $this
     */
    public function onceInHour()
    {
        return $this->onceInHours(1);
    }

    /**
     * Run Task one time in a day
     *
     * @return $this
     */
    public function onceInDay()
    {
        return $this->onceInDays(1);
    }

    /**
     * Run Task one time in a week
     *
     * @return $this
     */
    public function onceInWeek()
    {
        return $this->onceInWeeks(1);
    }

    /**
     * Run Task one time in a month
     *
     * @return $this
     */
    public function onceInMonth()
    {
        return $this->onceInMonths(1);
    }

    /**
     * Run Task one time in a quarter
     *
     * @return $this
     */
    public function onceInQuarter()
    {
        return $this->onceInQuarters(1);
    }

    /**
     * Adds a Task to a system queue that will be run one time only.
     *
     * @param string $type Available type of scheduling
     * @param Job $task The task to perform
     * @param int $extra The extra time that need to be adjusted.
     * @param int $timestamp The calculated $timestamp
     *
     * @return $this
     *
     * @uses Recurrence::calculateFromNow()
     *
     * @uses wp_schedule_single_event()
     */
    protected function registerOnce($type, Job $task, $extra = 1, $timestamp = null)
    {
        if ($this->has($type) && $this->isJobNotRegistered($task)) {

            $calculated = $timestamp;

            if (!$calculated) {
                $calculated = Recurrence::calculateFromNow($type, $extra);
            }

            wp_schedule_single_event($calculated, $task->name(), $task->args());
        }

        return $this;
    }
}
