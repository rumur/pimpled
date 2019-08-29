<?php

namespace Rumur\Pimpled\Scheduling;

class Recurrence
{
    /**
     * @var string
     */
    static protected $time_zone;

    /**
     * @return array
     */
    public static function multiple(): array
    {
        return apply_filters('pmld.scheduling.recurrence_multiple', [
            'every-minute' => [
                'interval' => MINUTE_IN_SECONDS,
                'display' => __('Every Minute', PMLD_TD),
            ],
            'every-five-minutes' => [
                'interval' => 5 * MINUTE_IN_SECONDS,
                'display' => __('Every 5 Minutes', PMLD_TD),
            ],
            'every-ten-minutes' => [
                'interval' => 10 * MINUTE_IN_SECONDS,
                'display' => __('Every 10 Minutes', PMLD_TD),
            ],
            'every-fifteen-minutes' => [
                'interval' => 15 * MINUTE_IN_SECONDS,
                'display' => __('Every 15 Minutes', PMLD_TD),
            ],
            'every-thirty-minutes' => [
                'interval' => 30 * MINUTE_IN_SECONDS,
                'display' => __('Every 30 Minutes', PMLD_TD),
            ],
            'hourly' => [
                'interval' => HOUR_IN_SECONDS,
                'display' => __('Hourly', PMLD_TD),
            ],
            'daily' => [
                'interval' => DAY_IN_SECONDS,
                'display' => __('Daily', PMLD_TD),
            ],
            'weekly' => [
                'interval' => WEEK_IN_SECONDS,
                'display' => __('Weekly', PMLD_TD),
            ],
            'monthly' => [
                'interval' => MONTH_IN_SECONDS,
                'display' => __('Monthly', PMLD_TD),
            ],
            'quarterly' => [
                'interval' => 3 * MONTH_IN_SECONDS,
                'display' => __('Quarterly', PMLD_TD),
            ],
            'yearly' => [
                'interval' => YEAR_IN_SECONDS,
                'display' => __('Yearly', PMLD_TD),
            ]
        ]);
    }

    /**
     * @return array
     */
    public static function single(): array
    {
        return apply_filters('pmld.scheduling.recurrence_single', [
            'minute' => [
                'interval' => MINUTE_IN_SECONDS,
                'display' => __('In a minute', PMLD_TD),
            ],
            'hour' => [
                'interval' => HOUR_IN_SECONDS,
                'display' => __('In one hour', PMLD_TD),
            ],
            'day' => [
                'interval' => DAY_IN_SECONDS,
                'display' => __('In a day', PMLD_TD),
            ],
            'week' => [
                'interval' => WEEK_IN_SECONDS,
                'display' => __('In a week', PMLD_TD),
            ],
            'month' => [
                'interval' => MONTH_IN_SECONDS,
                'display' => __('In a month', PMLD_TD),
            ],
            'quarter' => [
                'interval' => 3 * MONTH_IN_SECONDS,
                'display' => __('In a quarter', PMLD_TD),
            ],
            'year' => [
                'interval' => YEAR_IN_SECONDS,
                'display' => __('In a year', PMLD_TD),
            ],
        ]);
    }

    /**
     * @return array
     */
    public static function calculated(): array
    {
        return apply_filters('pmld.scheduling.recurrence_calculated', [
            'hourly-at' => [
                'interval' => HOUR_IN_SECONDS,
                'display' => __('Hourly at', PMLD_TD),
            ],
            'daily-at' => [
                'interval' => DAY_IN_SECONDS,
                'display' => __('Daily at', PMLD_TD),
            ],
            'weekly-on' => [
                'interval' => WEEK_IN_SECONDS,
                'display' => __('Weekly on', PMLD_TD),
            ],
            'monthly-on' => [
                'interval' => MONTH_IN_SECONDS,
                'display' => __('Monthly on', PMLD_TD),
            ],
        ]);
    }

    /**
     * @return array
     */
    public static function all(): array
    {
        return array_merge(static::single(), static::multiple(), static::calculated());
    }

    /**
     * @param string $type
     * @param int    $extra
     * @return int
     */
    public static function calculateFromNow($type, $extra = 1): int
    {
        $available = MINUTE_IN_SECONDS;

        if (static::has($type)) {
            $available = static::get($type);
            $available = $available['interval'];
        }

        return static::now() + ($available * $extra);
    }

    /**
     * @return int
     */
    public static function now(): int
    {
        return apply_filters('pmld.scheduling.now', time());
    }

    /**
     * @return string
     */
    public static function timeZone(): string
    {
        if (!static::$time_zone) {
            static::$time_zone = get_option('timezone_string');
        }

        return static::$time_zone;
    }

    /**
     * @param $type
     * @return array
     */
    public static function get($type): array
    {
        return static::all()[$type];
    }

    /**
     * Checks is the recurrence present in the list.
     *
     * @param string $recurrent
     *
     * @return bool
     */
    public static function has($recurrent): bool
    {
        return isset(static::all()[$recurrent]);
    }

    /**
     * Registers available recurrence list to the system.
     */
    public static function register()
    {
        add_filter('cron_schedules', static function(array $schedules) {
            return array_merge(static::multiple(), static::calculated(), $schedules);
        }, 10);
    }
}
