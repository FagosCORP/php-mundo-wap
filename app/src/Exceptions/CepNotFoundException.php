<?php

namespace App\Exceptions;

use App\Controller\Enum\HttpStatusCode\HttpStatusCode;
use Exception;

class CepNotFoundException extends Exception
{
    protected $message = 'CEP não encontrado';
    protected $code = HttpStatusCode::BAD_REQUEST;
}
