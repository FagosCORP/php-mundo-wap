<?php

namespace App\Exceptions;

use App\Controller\Enum\HttpStatusCode\ErrorStatus;
use Exception;

class CepNotFoundException extends Exception
{
    protected $message = 'CEP não encontrado';
    protected $code = ErrorStatus::BAD_REQUEST;
}
