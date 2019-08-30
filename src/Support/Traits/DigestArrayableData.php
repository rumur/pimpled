<?php


namespace Rumur\Pimpled\Support\Traits;


use Rumur\Pimpled\Contracts\Support\Arrayable;

trait DigestArrayableData
{
    /**
     * @param Arrayable | array $data
     *
     * @return array
     */
    protected function getArraybleData($data): array
    {
        return $data instanceof Arrayable ? $data->toArray() : (array) $data;
    }
}
