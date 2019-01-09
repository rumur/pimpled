<?php

namespace Pmld\Foundation\Http;

use \WP_Http as Status;
use \WP_Error as Driver;
use Pmld\Contracts\Http\Api\Response as ResponseContract;

class Response implements ResponseContract
{
    /** @var \WP_Error */
    protected $driver;

    /** @var int */
    protected $status = Status::OK;

    /**
     * Response constructor.
     */
    public function __construct()
    {
        $this->driver = new Driver();
    }

    /**
     * Factory.
     *
     * @param array $payload     See Response::add
     *
     * @return Response
     *
     * @author rumur
     */
    public static function make(array $payload)
    {
        $self = new static();

        $self->add( $payload );

        return $self;
    }

    /**
     * Checks the payload and add it ot the response.
     *
     * @param array|null $payload
     * @return $this
     *
     * @author rumur
     */
    protected function maybeAdd(array $payload = null)
    {
        if (! is_null($payload)) {
            $this->add($payload);
        }

        return $this;
    }

    /**
     * Prepare data for response.
     *
     * @param array $payload {
     *
     *     @type string       $handle          Response handle name.
     *     @type mixed        $data            Data which will be passed to the browser.
     *     @type int          $status          Server response status.
     *     @type string       $message         The Message for browser.
     * }
     * @return $this
     *
     * @author rumur
     */
    public function add(array $payload)
    {
        $message = '';

        if ($has_payload_message = isset($payload['message'])) {
            $message = $payload['message'];
            unset($payload['message']);
        }

        if ($has_payload_message = ! isset($payload['data'])) {
            $payload = ['data' => $payload];
        }

        /**
         * @var array $data
         * @var string $handle
         * @var string $message
         */
        extract( array_merge( [
            'data'    => [],
            'handle'  => uniqid(),
            'message' => $message,
        ], $payload ) );

        $this->driver->add( $handle, $message, $data );

        return $this;
    }

    /**
     * A Proxy to get all methods from the Driver.
     *
     * @param string $method
     * @param        $arguments
     *
     * @return mixed
     * @author rumur
     */
    public function __call($method, $arguments)
    {
        if ( is_callable( [ $this->driver, $method ], true ) ) {
            return call_user_func_array( [ $this->driver, $method ], $arguments );
        }

        return null;
    }

    /**
     * @param array|null $payload
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
    public function ok(array $payload = null)
    {
        return $this->maybeAdd($payload)->dispatch(Status::OK);
    }

    /**
     * @param array|null $payload
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
    public function notFound(array $payload = null)
    {
        return $this->maybeAdd($payload)->dispatch(Status::NOT_FOUND);
    }

    /**
     * @param array|null $payload
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
    public function forbidden(array $payload = null)
    {
        return $this->maybeAdd($payload)->dispatch(Status::FORBIDDEN);
    }

    /**
     * @param array|null $payload
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
    public function serverError(array $payload = null)
    {
        return $this->maybeAdd($payload)->dispatch(Status::INTERNAL_SERVER_ERROR);
    }

    /**
     * @param array|null $payload
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
    public function unAuthorized(array $payload = null)
    {
        return $this->maybeAdd($payload)->dispatch(Status::UNAUTHORIZED);
    }

    /**
     * Adds a payload and Send back on Request.
     *
     * @param array $payload    Response payload
     * @param int|null $status  Response code
     *
     * @author rumur
     *
     * @return mixed|\WP_REST_Response
     */
    public function dispatchWith(array $payload, $status = null)
    {
        return $this->add($payload)->dispatch($status ?: $this->status);
    }

    /**
     * Send back on Request.
     *
     * @param int $status HTTP header status code.
     *
     * @see \WP_REST_Server::error_to_response
     *
     * @uses \apply_filters()
     * @uses \WP_REST_Response::class
     *
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
    public function dispatch($status)
    {
        $errors = [];
        $driver = $this->driver;
        $status = absint($status);

        foreach ( (array) $driver->errors as $code => $messages ) {

            foreach ( (array) $messages as $message ) {

                $passed_data = $driver->get_error_data($code);

                $errors[] = [
                    'code' => $code,
                    'message' => $message,
                    'data' => $passed_data,
                ];
            }
        }

        $data = $errors[0];

        if ( count( $errors ) > 1 ) {
            // Remove the primary error.
            array_shift( $errors );
            $data['additional_errors'] = $errors;
        }

        $response = new \WP_REST_Response($data, $status);

        $response = \apply_filters("pmld.dispatch_response_{$status}", $response, $data);

        return \apply_filters('pmld.dispatch_response', $response, $data, $status);
    }
}
