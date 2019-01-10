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
    protected function process(array $data)
    {
        return array_diff_key($data, array_fill_keys($this->hidden, false));
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
