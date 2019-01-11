<?php

namespace Pmld\Http\Exceptions;

use Pmld\Contracts\Http\RequestsException as RequestsExceptionContracts;

class UnauthorizedException extends \Requests_Exception_HTTP_401 implements RequestsExceptionContracts
{

}
