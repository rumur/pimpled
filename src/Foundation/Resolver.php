<?php

namespace Rumur\Pimpled\Foundation;

use \ReflectionClass;
use \ReflectionParameter;

class Resolver
{
    /** @var Application */
    protected $app;

    /**
     * Resolver constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $class
     * @return object
     * @throws \ReflectionException | \Exception
     */
    public function resolve($class)
    {
        // If we have a binding for it, then it's a closure.
        // We can just invoke it and return the resolved instance.
//        if ($this->app->has($class)) {
//            return $this->app->get($class)($this);
//        }

        // Otherwise we are going to try and use reflection to "autowire"
        // the dependencies and instantiate this entry if it's a class.
        if (! class_exists($class)) {
            throw ResolverNotFoundException::make($class);
        }

        $reflector = new ReflectionClass($class);

        // If the reflector is not instantiable, it's probably an interface.
        // In that case the user should register a factory, since we can't possibly know what
        // concrete class they want.  It could also be an abstract class, which we can't build either.
        if (!$reflector->isInstantiable()) {
            throw ResolverNotFoundException::make($class);
        }

        /** @var \ReflectionMethod|null */
        $constructor = $reflector->getConstructor();

        // If there isn't a constructor, there aren't any dependencies.
        // We can just instantiate the class and return it without doing anything.
        if (is_null($constructor)) {
            return new $class;
        }

        $params = $constructor->getParameters();

        if (0 === count($params)) {
            return new $class;
        }

        // Otherwise we need to go through and recursively build all of the dependencies.

        $dependencies = array_map(function (ReflectionParameter $dependency) use ($class) {

            if (is_null($dependency->getClass())) {
                throw ResolverNotFoundException::make($class);
            }

            return $this->app->get($dependency->getClass()->name);

        }, $constructor->getParameters());


        return $reflector->newInstanceArgs($dependencies);
    }
}
