<?php

namespace Rumur\Pimpled\Http\Exceptions;

use Rumur\Pimpled\Contracts\Http\RequestsException as RequestsExceptionContracts;

class RequestsException extends \Requests_Exception_HTTP implements RequestsExceptionContracts
{

}
