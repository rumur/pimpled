<?php

namespace Rumur\Pimpled\Support;

use Rumur\Pimpled\Contracts\Scheduling\Job;
use Rumur\Pimpled\Foundation\Application;

if (!function_exists('dd')) {
    /**
     * Debug function.
     * Makes a var_dump of given args.
     */
    function dd()
    {
        var_dump(...func_get_args());
        exit;
    }
}

if (!function_exists('td')) {
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
}

if (!function_exists('tp')) {
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
}

if (!function_exists('value')) {
    /**
     * Gets the passed data
     *
     * @param mixed $thing
     *
     * @return mixed
     */
    function value($thing)
    {
        return $thing instanceof \Closure ? $thing() : $thing;
    }
}

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string $abstract
     * @param array $value
     *
     * @return mixed|\Rumur\Pimpled\Foundation\Application
     */
    function app($abstract = null, array $value = [])
    {
        if ($abstract === null) {
            return Application::getInstance();
        }

        return Application::getInstance()->offsetExists($abstract)
            ? Application::getInstance()->offsetGet($abstract)
            : Application::getInstance()->instance($abstract, $value);
    }
}

if (!function_exists('app_path')) {
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
}

if (!function_exists('base_path')) {
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
}

if (!function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param mixed $value
     * @return Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}

if (!function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param array|string $key
     * @param mixed $default
     *
     * @return mixed|\Rumur\Pimpled\Config\Repository
     */
    function config($key = null, $default = null)
    {
        if ($key === null) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (!function_exists('config_path')) {
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
}

if (!function_exists('database_path')) {
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
}

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed $target
     * @param string|array|int $key
     * @param mixed $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if ($key === null) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
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
}

if (!function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param mixed $target
     * @param string|array $key
     * @param mixed $value
     * @param bool $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (!Arr::accessible($target)) {
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
                if (!Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (!isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target->{$segment})) {
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
}

if (!function_exists('logger')) {
    /**
     * Log a debug message to the logs.
     *
     * @param string $message
     * @param array $context
     *
     * @return LogManager
     */
    function logger($message = null, array $context = [])
    {
        if ($message === null) {
            return app('log');
        }

        return app('log')->debug($message, $context);
    }
}

if (!function_exists('logs')) {
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
}

if (!function_exists('public_path')) {
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
}

if (!function_exists('uploads_path')) {
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
}

if (!function_exists('schedule')) {
    /**
     * Get the schedule instance of the given job.
     *
     * @param Job $job
     * @return \Rumur\Pimpled\Scheduling\Scheduler
     */
    function schedule(Job $job = null)
    {
        $factory = app('schedule');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->job($job);
    }
}

if (!function_exists('storage_path')) {
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
}

if (!function_exists('resources_path')) {
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
}

if (!function_exists('validator')) {
    /**
     * Create a new validator instance.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     *
     * @return Rumur\Pimpled\Validation\ValidationFactory|\Rumur\Pimpled\Validation\Validator
     */
    function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
    {
        /** @var Rumur\Pimpled\Validation\ValidationFactory $factory */
        $factory = app(ValidationFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($data, $rules, $messages, $customAttributes);
    }
}

if (!function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array $data
     * @return \Rumur\Pimpled\Contracts\View\View|\Rumur\Pimpled\Contracts\View\Factory
     */
    function view($view = null, $data = [])
    {
        $factory = app('view');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data);
    }
}

if (!function_exists('route')) {
    /**
     * Generate the URL to a named route.
     *
     * @param string $name
     * @param mixed $parameters
     * @param string $locale
     * @return string
     */
    function route($name, $parameters = [], $locale = null)
    {
        return app('url')->route($name, $parameters, $locale);
    }
}
