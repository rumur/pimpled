<?php

namespace Rumur\Pimpled\Contracts\View;

use Rumur\Pimpled\Contracts\Support\Arrayable;

interface Factory
{
    /**
     * Determine if a given view exists.
     *
     * @param  string  $view
     * @return bool
     */
    public function exists($view);

    /**
     * Get the first view that actually exists from the given list.
     *
     * @param  array  $views
     * @param  Arrayable | array $data
     *
     * @return View
     */
    public function first(array $views, $data = []);

    /**
     * @param string $view
     * @param Arrayable|array  $data
     * @return View
     */
    public function make($view, $data = []);

    /**
     * Add a piece of shared data to the environment.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function share($key, $value = null);
}
