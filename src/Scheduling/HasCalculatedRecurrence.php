<?php

namespace Rumur\Pimpled\Scheduling;

use Closure;
use DateTime;
use Exception;
use InvalidArgumentException;

trait HasCalculatedRecurrence
{
    /**
     * Run the task every hour
     *
     * @param int $min e.g. at 15 past the hour
     * @param Closure $cb Callback that might fix the timestamp.
     * @hook  pmld.scheduling_recurrence_hourly_at
     *
     * @return $this
     */
    public function hourlyAt($min, Closure $cb = null)
    {
        try {

            $orig_min = $min;

            $min = max(0, min($min, 59));

            $today = new DateTime('now');

            $time = sprintf('%s:%s', $today->format('H'), $min);

            $current_hour_ts = (new DateTime("today {$time}"))->getTimestamp();
            $next_hour_ts = (new DateTime("today {$time}"))->modify('+1 hour')->getTimestamp();

            $timestamp = $this->now() > $current_hour_ts ? $next_hour_ts : $current_hour_ts;

            if (($cb instanceof Closure) && $fixed_timestamp = $cb($timestamp, $orig_min)) {
                $timestamp = $fixed_timestamp;
            }

            $this->registerRecurrence('hourly-at', $this->resolveTask(),
                apply_filters('pmld.scheduling.hourly_at', $timestamp, $min, $orig_min));

        } catch (Exception $e) {
            throw new InvalidArgumentException("The wrong {$min} has been provided");
        }

        return $this;
    }

    /**
     * Run the task every day
     *
     * @param string $time e.g. at 13:00
     * @param Closure $cb Callback that might fix the timestamp.
     *
     * @hook pmld.scheduling_recurrence_daily_at
     *
     * @return $this
     * @throws Exception
     */
    public function dailyAt($time, Closure $cb = null)
    {
        try {

            $parsed_time = $this->parseTime($time);

            $today_ts = $this->fixGMTTimeStamp((new DateTime("today {$parsed_time}"))->getTimestamp());
            $tomorrow_ts = $this->fixGMTTimeStamp((new DateTime("tomorrow {$parsed_time}"))->getTimestamp());

            $timestamp = $this->now() > $today_ts ? $tomorrow_ts : $today_ts;

            if (($cb instanceof Closure) && $fixed_timestamp = $cb($timestamp, $time)) {
                $timestamp = $fixed_timestamp;
            }

            $this->registerRecurrence('daily-at', $this->resolveTask(),
                apply_filters('pmld.scheduling.daily_at', $timestamp, $time));

        } catch (Exception $e) {
            throw new InvalidArgumentException("The wrong {$time} has been provided");
        }

        return $this;
    }


    /**
     * Run the task every week on particular day and particular time.
     *
     * @param int $day_num e.g. 1-7
     * @param string $time e.g. 14:30
     * @param Closure $cb Callback that might fix the timestamp.
     * @hook  pmld.scheduling_recurrence_weekly_on
     * @return $this
     */
    public function weeklyOn($day_num, $time, Closure $cb = null)
    {
        try {
            // Fixing the offset of the days.
            $day_num = max(0, min(--$day_num, 6));

            $parsed_time = $this->parseTime($time);

            /**
             * Relative day
             * @link https://www.php.net/manual/ru/datetime.formats.relative.php
             */
            $week_day_list = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

            $this_week = new DateTime(sprintf('%s this week %s', $week_day_list[$day_num], $parsed_time));
            $next_week = new DateTime(sprintf('%s next week %s', $week_day_list[$day_num], $parsed_time));

            $this_week_ts = $this->fixGMTTimeStamp($this_week->getTimestamp());
            $next_week_ts = $this->fixGMTTimeStamp($next_week->getTimestamp());

            $timestamp = $this->now() >= $this_week_ts ? $next_week_ts : $this_week_ts;

            if (($cb instanceof Closure) && $fixed_timestamp = $cb($timestamp, $time)) {
                $timestamp = $fixed_timestamp;
            }

            $this->registerRecurrence('weekly-on', $this->resolveTask(),
                apply_filters('pmld.scheduling.weekly_on', $timestamp, $day_num, $time));

        } catch (Exception $e) {
            throw new InvalidArgumentException(sprintf('The wrong params %s has been provided', print_r(func_get_args(), true)));
        }

        return $this;
    }

    /**
     * Run the task every month
     *
     * @NOTE Be careful with this recurrence, It doesn't count the last days of the month,
     *       It might shift the days due to day specific, such 31 or 28 of February.
     *
     *       Example:
     *             Current day is 15th of June.
     *             Params are `31` and `15:30`, so as June doesn't have 31st day it's gonna set up a July 1st instead.
     *
     * @param string $month_day e.g. 1-31
     * @param string $time e.g. 15:00
     * @param Closure $cb Callback that might fix the timestamp.
     * @hook  pmld.scheduling_recurrence_monthly_on
     * @return $this
     */
    public function monthlyOn($month_day, $time, Closure $cb = null)
    {
        try {
            $time = $this->parseTime($time);

            $month_day = max(1, min($month_day, 31));

            $today = new DateTime("today {$time}");

            $current_month = $today->format('Y {j} M H:i');

            $fixed_date = preg_replace('/{[\d]+}/', $month_day, $current_month);

            $current_month_ts = $this->fixGMTTimeStamp(DateTime::createFromFormat($format = 'Y j M H:i', $fixed_date)->getTimestamp());
            $next_month_ts = $this->fixGMTTimeStamp(DateTime::createFromFormat($format, $fixed_date)->modify('+1 month')->getTimestamp());

            $timestamp = $this->now() >= $current_month_ts ? $next_month_ts : $current_month_ts;

            if (($cb instanceof Closure) && $fixed_timestamp = $cb($timestamp, $month_day, $time)) {
                $timestamp = $fixed_timestamp;
            }

            $this->registerRecurrence('monthly-on', $this->resolveTask(),
                apply_filters('pmld.scheduling.monthly_on', $timestamp, $month_day, $time));

        } catch (Exception $e) {
            throw new InvalidArgumentException(sprintf('The wrong params %s has been provided', print_r(func_get_args(), true)));
        }

        return $this;
    }

    /**
     * @param integer $timestamp
     * @return float|int
     */
    protected function fixGMTTimeStamp($timestamp)
    {
        return $timestamp - (get_option( 'gmt_offset' ) * HOUR_IN_SECONDS);
    }
}
