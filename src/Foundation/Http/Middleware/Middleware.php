<?php

namespace Pmld\Foundation\Http\Middleware;

use Pmld\Support\Facades\Request;

use Pmld\Contracts\Http\Api\Middleware as MiddlewareContract;

abstract class Middleware implements MiddlewareContract {

    /** @var \Pmld\Foundation\Http\Request */
    protected $request;

    /**
     * Middleware constructor.
     */
    public function __construct()
    {
        $this->boot();
    }

    /**
     * Boots all necessary stuff.
     *
     * @author rumur
     */
    public function boot()
    {
        $this->request = Request::getInstance();
    }
}
