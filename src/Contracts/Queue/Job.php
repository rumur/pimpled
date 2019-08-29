<?php

namespace Rumur\Pimpled\Contracts\Queue;

interface Job
{
    /**
     * Set Payload used during the request
     *
     * @param array $payload Payload.
     *
     * @return $this
     */
    public function set(array $payload);

    /**
     * Dispatch the async request
     *
     * @return array|\WP_Error
     */
    public function dispatch();

    /**
     * Maybe handle
     *
     * Check for correct nonce and pass to handler.
     */
    public function maybeHandle();
}
