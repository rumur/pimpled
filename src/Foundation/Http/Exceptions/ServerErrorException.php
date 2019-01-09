<?php

namespace Pmld\Foundation\Http\Exceptions;

use Pmld\Contracts\Http\RequestsException as RequestsExceptionContracts;

class ServerErrorException extends \Requests_Exception_HTTP_500 implements RequestsExceptionContracts
{

}
