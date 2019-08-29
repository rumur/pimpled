<?php

namespace Rumur\Pimpled\Http\Exceptions;

use Rumur\Pimpled\Contracts\Http\RequestsException as RequestsExceptionContracts;

class UnauthorizedException extends \Requests_Exception_HTTP_401 implements RequestsExceptionContracts
{

}
