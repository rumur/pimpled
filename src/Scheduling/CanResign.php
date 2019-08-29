<?php


namespace Rumur\Pimpled\Scheduling;

use InvalidArgumentException;
use Rumur\Pimpled\Contracts\Scheduling\Job as JobContract;

trait CanResign
{
    /**
     * Resign a particular job
     *
     * @param JobContract|null $task
     *
     * @return $this
     * @throws InvalidArgumentException
     *
     * @uses wp_next_scheduled()
     * @uses wp_clear_scheduled_hook()
     *
     */
    public function resignJob(JobContract $task = null)
    {
        $task = $this->resolveTask($task);

        if (wp_next_scheduled($task->name(), $task->args())) {
            wp_clear_scheduled_hook($task->name(), $task->args());
        }

        return $this;
    }

    /**
     * Resign all jobs for a provided task.
     *
     * @param JobContract|null $task
     *
     * @return $this
     * @uses wp_unschedule_hook()
     *
     */
    public function resignAllJobs(JobContract $task = null)
    {
        wp_unschedule_hook($this->resolveTask($task)->name());

        return $this;
    }
}
