<?php

namespace Rumur\Pimpled\Support;

use Rumur\Pimpled\Contracts\Support\Arrayable;
use Rumur\Pimpled\Contracts\Support\Transformable;

abstract class Transformer implements Transformable
{
    /**
     * The hidden keys from transformation
     */
    protected $hidden = [];

    /**
     * Hides hidden fields within passed data
     *
     * @param Arrayable|array $data
     * @return array
     */
    protected function process($data = []): array
    {
        return array_diff_key($data instanceof Arrayable ? $data->toArray() : $data,
            array_fill_keys($this->hidden, false));
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
