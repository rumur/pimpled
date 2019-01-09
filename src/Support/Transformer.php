<?php

namespace Pmld\Support;

use Pmld\Contracts\Support\Transformable;

abstract class Transformer implements Transformable
{
    /**
     * The hidden keys from transformation
     */
    protected $hidden = [];

    /**
     * Hides hidden fields within passed data
     *
     * @param array $data
     * @return array
     */
    protected function hide(array $data)
    {
        return array_diff_key($data, array_fill_keys($this->hidden, false));
    }
}
