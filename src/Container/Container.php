<?php

namespace Rumur\Pimpled\Container;

use Pimple\Container as BaseContainer;

class Container extends BaseContainer
{
    /**
     * @var Container
     */
    protected static $instance = null;

    /**
     * @param Container $app
     */
    protected static function setInstance(Container $app)
    {
        if (static::$instance === null) {
            static::$instance = $app;
        }
    }

    /**
     * @return Container|null
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function instance($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this->offsetGet($key);
    }

    /**
     * Psr11 Container
     *
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Psr11 Container
     *
     * @param $id
     * @return bool
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * Dynamically access container services.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this[$key];
    }

    /**
     * Dynamically set container services.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }

    /**
     * Check if a piece of data is bound to the container.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this[$key]);
    }
}
