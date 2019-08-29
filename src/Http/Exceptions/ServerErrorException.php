<?php

namespace Rumur\Pimpled\Http\Exceptions;

use Rumur\Pimpled\Contracts\Http\RequestsException as RequestsExceptionContracts;

class ServerErrorException extends \Requests_Exception_HTTP_500 implements RequestsExceptionContracts
{

}
