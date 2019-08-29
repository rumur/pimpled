<?php

namespace Rumur\Pimpled\Foundation;

class ResolverNotFoundException extends \Exception
{
    /**
     * @param $class
     * @return ResolverNotFoundException
     */
    public static function make($class)
    {
        return new static(sprintf('No container definition was found for "%s"', $class));
    }
}
