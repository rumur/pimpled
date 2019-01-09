<?php

namespace Pmld;

use Pmld\Support\Arr;
use Pmld\Support\Collection;
use Pmld\Foundation\Application;


/**
 * Return the default value of the given value.
 *
 * @param  mixed  $value
 * @return mixed
 */
function value($value)
{
    return $value instanceof \Closure ? $value() : $value;
}


/**
 * Debug function.
 * Makes a var_dump of given args.
 */
function dd()
{
    var_dump(... func_get_args());

    die(1);
}



/**
 * Makes a pretty print_r of given args, but terminates the stream.
 *
 * @param $args
 */
function td($args)
{
    tp($args);
    exit;
}

/**
 * Makes a pretty print_r of given args.
 *
 * @param $args
 */
function tp($args)
{
    echo '<pre>';
    print_r($args);
    echo '</pre>';
}

/**
 * Get the available container instance.
 *
 * @param string $abstract
 * @param array  $value
 *
 * @return mixed|\Pmld\Foundation\Application
 */
function app($abstract = null, array $value = [])
{
    if (is_null($abstract)) {
        return Application::getInstance();
    }

    return Application::getInstance()->offsetExists($abstract)
        ? Application::getInstance()->offsetGet($abstract)
        : Application::getInstance()->instance($abstract, $value);
}

/**
 * Get the application path.
 *
 * @param string $path
 *
 * @return string
 */
function app_path($path = '')
{
    return app()->basePath('app' . DIRECTORY_SEPARATOR . $path);
}

/**
 * Get the path to the base of the install.
 *
 * @param string $path
 *
 * @return string
 */
function base_path($path = '')
{
    return app()->basePath($path);
}

/**
 * Get / set the specified configuration value.
 *
 * If an array is passed as the key, we will assume you want to set an array of values.
 *
 * @param array|string $key
 * @param mixed        $default
 *
 * @return mixed|\Pmld\Config\Repository
 */
function config($key = null, $default = null)
{
    if (is_null($key)) {
        return app('config');
    }

    if (is_array($key)) {
        return app('config')->set($key);
    }

    return app('config')->get($key, $default);
}

/**
 * Return the root config path.
 *
 * @param string $path
 *
 * @return string
 */
function config_path($path = '')
{
    return app()->configPath($path);
}

/**
 * Get the database path.
 *
 * @param string $path
 *
 * @return string
 */
function database_path($path = '')
{
    return app()->databasePath($path);
}

/**
 * Get the database path.
 *
 * @param string $path
 *
 * @return string
 */
function public_path($path = '')
{
    return app()->publicPath($path);
}

/**
 * Get the uploads path.
 *
 * @param string $path
 *
 * @return string
 */
function uploads_path($path = '')
{
    return app()->uploadsPath($path);
}

/**
 * Get the path to the storage folder.
 *
 * @param string $path
 *
 * @return string
 */
function storage_path($path = '')
{
    return app()->storagePath($path);
}

/**
 * Get the path to the storage folder.
 *
 * @param string $path
 *
 * @return string
 */
function resources_path($path = '')
{
    return app()->resourcesPath($path);
}

/**
 * Log a debug message to the logs.
 *
 * @param string $message
 * @param array  $context
 *
 * @return LogManager
 */
function logger($message = null, array $context = [])
{
    if (is_null($message)) {
        return app('log');
    }

    return app('log')->debug($message, $context);
}

/**
 * Get a log driver instance.
 *
 * @param string $driver
 *
 * @return LogManager|LoggerInterface
 */
function logs($driver = null)
{
    return $driver ? app('log')->driver($driver) : app('log');
}

/**
 * Create a new validator instance.
 *
 * @param array $data
 * @param array $rules
 * @param array $messages
 * @param array $customAttributes
 *
 * @return Pmld\Validation\ValidationFactory|\Pmld\Validation\Validator
 */
function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
{
    /** @var Pmld\Validation\ValidationFactory $factory */
    $factory = app(ValidationFactory::class);

    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->make($data, $rules, $messages, $customAttributes);
}

/**
 * Get an item from an array or object using "dot" notation.
 *
 * @param  mixed   $target
 * @param  string|array|int  $key
 * @param  mixed   $default
 * @return mixed
 */
function data_get($target, $key, $default = null)
{
    if (is_null($key)) {
        return $target;
    }

    $key = is_array($key) ? $key : explode('.', $key);

    while (! is_null($segment = array_shift($key))) {
        if ($segment === '*') {
            if ($target instanceof Collection) {
                $target = $target->all();
            } elseif (! is_array($target)) {
                return value($default);
            }

            $result = [];

            foreach ($target as $item) {
                $result[] = data_get($item, $key);
            }

            return in_array('*', $key) ? Arr::collapse($result) : $result;
        }

        if (Arr::accessible($target) && Arr::exists($target, $segment)) {
            $target = $target[$segment];
        } elseif (is_object($target) && isset($target->{$segment})) {
            $target = $target->{$segment};
        } else {
            return value($default);
        }
    }

    return $target;
}

/**
 * Set an item on an array or object using dot notation.
 *
 * @param  mixed  $target
 * @param  string|array  $key
 * @param  mixed  $value
 * @param  bool  $overwrite
 * @return mixed
 */
function data_set(&$target, $key, $value, $overwrite = true)
{
    $segments = is_array($key) ? $key : explode('.', $key);

    if (($segment = array_shift($segments)) === '*') {
        if (! Arr::accessible($target)) {
            $target = [];
        }

        if ($segments) {
            foreach ($target as &$inner) {
                data_set($inner, $segments, $value, $overwrite);
            }
        } elseif ($overwrite) {
            foreach ($target as &$inner) {
                $inner = $value;
            }
        }
    } elseif (Arr::accessible($target)) {
        if ($segments) {
            if (! Arr::exists($target, $segment)) {
                $target[$segment] = [];
            }

            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite || ! Arr::exists($target, $segment)) {
            $target[$segment] = $value;
        }
    } elseif (is_object($target)) {
        if ($segments) {
            if (! isset($target->{$segment})) {
                $target->{$segment} = [];
            }

            data_set($target->{$segment}, $segments, $value, $overwrite);
        } elseif ($overwrite || ! isset($target->{$segment})) {
            $target->{$segment} = $value;
        }
    } else {
        $target = [];

        if ($segments) {
            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite) {
            $target[$segment] = $value;
        }
    }

    return $target;
}
