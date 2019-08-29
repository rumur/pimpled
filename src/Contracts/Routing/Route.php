<?php

namespace Rumur\Pimpled\Contracts\Routing;

use Serializable;
use Rumur\Pimpled\Contracts\Support\Arrayable;
use Rumur\Pimpled\Contracts\Support\Stringable;

interface Route extends Arrayable, Serializable, Stringable
{
    /**
     * Gets the route name if it's a named route
     *
     * @return string
     */
    public function name(): string;

    /**
     * Determines whether the the route has a name.
     *
     * @return bool
     */
    public function hasName(): bool;

    /**
     * Determines which request methods the route is listening to.
     *
     * @return array
     */
    public function methods(): array;

    /**
     * Determines if the route uri
     *
     * @return string
     */
    public function uri(): string;

    /**
     * Determines if the route has params
     *
     * @return bool
     */
    public function hasParams(): bool;

    /**
     * Represents parsed params without their url placeholders
     *
     * @return array    e.g. [ member_id ]
     */
    public function params(): array;

    /**
     * Represents parsed params with their url placeholders,
     *
     * @return array e.g. [ member_id => {member_id} ]
     */
    public function paramsWithUrlPlaceholder(): array;

    /**
     * Makes the url with passed params.
     *
     * @param Arrayable | array $parameters
     *
     * @return string
     */
    public function urlFromParams($parameters): string;
}
