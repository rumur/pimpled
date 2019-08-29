<?php

namespace Rumur\Pimpled\Routing;

use Rumur\Pimpled\Contracts\Routing\Route as RouteContract;

class NullRoute implements RouteContract
{
    public function uri(): string
    {
        return home_url('/');
    }

    public function name(): string
    {
        return '';
    }

    public function methods(): array
    {
        return ['GET'];
    }

    public function params(): array
    {
        return [];
    }

    public function urlFromParams($parameters): string
    {
        return $this->uri();
    }

    public function hasName(): bool
    {
        return false;
    }

    public function paramsWithUrlPlaceholder(): array
    {
        return [];
    }

    public function hasParams(): bool
    {
        return false;
    }

    public function toArray(): array
    {
        return [
            'methods' => $this->methods(),
            'name' => $this->name(),
            'url' => $this->uri(),
        ];
    }

    public function serialize()
    {
        return '';
    }

    public function unserialize($serialized, $options = null)
    {
        return [];
    }

    public function __toString(): string
    {
        return $this->uri();
    }
}
