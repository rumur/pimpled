<?php

namespace Rumur\Pimpled\Contracts\View;

use Rumur\Pimpled\Contracts\Support\Renderable;
use Rumur\Pimpled\Contracts\Support\Stringable;

interface View extends Renderable, Stringable
{
    /**
     * Makes the HTML from the passed $name and $data
     *
     * @return string
     */
    public function content(): string;

    /**
     * Shared params to all nested Views.
     *
     * @param string|array $params
     * @param mixed $value
     *
     * @return View
     */
    public function share($params, $value = null);

    /**
     * Adds vars to the tpl.
     *
     * @param mixed $params
     * @param mixed $value
     *
     * @return View
     */
    public function with($params, $value = null);
}
