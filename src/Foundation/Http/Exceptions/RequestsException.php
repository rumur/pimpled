<?php

namespace Pmld\Foundation\Http\Exceptions;

use Pmld\Contracts\Http\RequestsException as RequestsExceptionContracts;

class RequestsException extends \Requests_Exception_HTTP implements RequestsExceptionContracts
{

}
