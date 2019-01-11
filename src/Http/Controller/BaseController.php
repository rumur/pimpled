<?php

namespace Pmld\Http\Controller;

use Pmld\Contracts\Http\Api\Response;

abstract class BaseController extends \WP_REST_Controller
{
    /** @var \WP_REST_Request request */
	protected $request;

    /** @var \Pmld\Http\Response response */
	protected $response;

	/**
	 * BaseController constructor.
     *
     * @param \WP_REST_Request $request
     * @param Response $response
	 */
	public function __construct(\WP_REST_Request $request, Response $response)
	{
        $this->request  = $request;
        $this->response = $response;
	}
}
