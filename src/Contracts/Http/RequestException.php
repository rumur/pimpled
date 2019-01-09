<?php

namespace Pmld\Contracts\Http;

interface RequestsException
{
    /**
     * Get the status message
     */
    public function getReason();
}
