<?php

defined('ABSPATH') || die();

return [
    /* --------------------------------------------------------------- */
    // Plugin available cron scheduled tasks
    /* --------------------------------------------------------------- */
    'jobs' => [
        Pmld\App\Scheduled\DummyJob::class,
    ]
];
