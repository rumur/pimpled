<?php

namespace Rumur\Pimpled\Contracts\Http;

interface RequestsException
{
    /**
     * Get the status message
     */
    public function getReason();
}
