<?php

namespace Pmld\App\Api\Http\Middleware;

use Pmld\Contracts\Http\Request;
use Pmld\Foundation\Http\Middleware\Middleware;

class LoggedOutMiddleware extends Middleware
{
    /** @inheritdoc */
    public function handle(Request $request)
    {
        return ! is_user_logged_in();
    }
}
