<?php

namespace Rumur\Pimpled\Contracts\Scheduling;

interface Job
{
    /**
     * Returns the job action name that the system can recognize it.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Gets args for that Job.
     *
     * @return null|array
     */
    public function args();

    /**
     * The Job handler, it's where the main job is being done
     *
     * @return mixed
     */
    public function handle();
}
