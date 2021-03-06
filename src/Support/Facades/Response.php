<?php

namespace Rumur\Pimpled\Support\Facades;

/**
 * Class Response
 *
 * @method static mixed|\WP_REST_Response dispatch($status)
 * @method static mixed|\WP_REST_Response ok(array $payload = null)
 * @method static mixed|\WP_REST_Response notFound(array $payload = null)
 * @method static mixed|\WP_REST_Response forbidden(array $payload = null)
 * @method static mixed|\WP_REST_Response serverError(array $payload = null)
 * @method static mixed|\WP_REST_Response unAuthorized(array $payload = null)
 * @method static \Rumur\Pimpled\Http\Response add(array $payload)
 * @method static mixed|\WP_REST_Response dispatchWith(array $payload, $status = null)
 *
 * @see namespace Rumur\Pimpled\Http\Response
 */
class Response extends Facade
{
    /**
     * Return the service provider key responsible for the request class.
     * The key must be the same as the one used when registering
     * the service provider.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'response';
    }
}
