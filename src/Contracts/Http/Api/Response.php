<?php

namespace Pmld\Contracts\Http\Api;

interface Response
{
	/**
	 * Factory.
	 *
	 * @param array $payload     See Response::add
	 *
	 * @return Response
	 *
	 * @author rumur
	 */
	public static function make(array $payload);

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
	public function add(array $payload);

    /**
     * Terminate server and return the response to user.
     *
     * @param int $status HTTP header status code.
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
	public function dispatch($status);

    /**
     * @param array|null $payload
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
    public function ok(array $payload = null);

    /**
     * @param array|null $payload
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
    public function notFound(array $payload = null);

    /**
     * @param array|null $payload
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
    public function forbidden(array $payload = null);

    /**
     * @param array|null $payload
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
    public function serverError(array $payload = null);

    /**
     * @param array|null $payload
     * @return mixed|\WP_REST_Response
     *
     * @author rumur
     */
    public function unAuthorized(array $payload = null);

    /**
     * Adds a payload and Send back on Request.
     *
     * @param array $payload    Response payload
     * @param int|null $status  Response code
     *
     * @author rumur
     */
    public function dispatchWith(array $payload, $status = null);
}
