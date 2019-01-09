<?php

namespace Pmld\Contracts\Http\Api;

use Pmld\Contracts\Http\Request;

Interface Middleware
{
    /**
     * Checks if a given request has access.
     *
     * @param Request  $request Full details about the request.
     * @see `wp-includes/rest-api.php:rest_send_allow_header`
     *
     * @return \WP_Error|bool True if the request has access, error object otherwise.
     *
     * @author rumur
     */
    public function handle(Request $request);
}
