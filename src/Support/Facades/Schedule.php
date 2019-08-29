<?php

namespace Rumur\Pimpled\Support\Facades;

use Rumur\Pimpled\Scheduling\Job;

/**
 * Class Schedule
 *
 * @method static \Rumur\Pimpled\Scheduling\Scheduler job(Job $task)
 * @method static \Rumur\Pimpled\Scheduling\Scheduler resignJob(Job $task = null)
 * @method static \Rumur\Pimpled\Scheduling\Scheduler resignAllJobs(Job $task = null)
 * @method static bool|int isJobRegistered(Job $task = null)
 * @method static bool|int isJobNotRegistered(Job $task = null)
 *
 * @see \Rumur\Pimpled\Scheduling\Scheduler
 */
class Schedule extends Facade
{
    /**
     * Return the service provider key responsible for the request class.
     * The key must be the same as the one used when registering
     * the service provider.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'schedule';
    }
}
