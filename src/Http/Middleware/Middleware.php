<?php

namespace Rumur\Pimpled\Http\Middleware;

use Rumur\Pimpled\Support\Facades\Request;

use Rumur\Pimpled\Contracts\Http\Api\Middleware as MiddlewareContract;

abstract class Middleware implements MiddlewareContract {

    /** @var \Rumur\Pimpled\Http\Request */
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
