<?php

namespace Pmld\App\Scheduled;

use Exception;
use Rumur\Pimpled\Scheduling\Job;

class DummyJob extends Job
{
    /**
     * @return mixed|void
     * @throws Exception
     */
    public function handle()
    {
        // Do you dummy task here
    }
}
