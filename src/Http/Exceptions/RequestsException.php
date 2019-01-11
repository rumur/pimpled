<?php

namespace Pmld\Http\Exceptions;

use Pmld\Contracts\Http\RequestsException as RequestsExceptionContracts;

class RequestsException extends \Requests_Exception_HTTP implements RequestsExceptionContracts
{

}
