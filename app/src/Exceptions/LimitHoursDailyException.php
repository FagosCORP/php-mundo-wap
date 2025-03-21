<?php

namespace App\Exceptions;

use App\Controller\Enum\HttpStatusCode\HttpStatusCode;
use Exception;

class LimitHoursDailyException extends Exception
{
    protected $message = 'Limite de horas atingidos.';
    protected $code = HttpStatusCode::BAD_REQUEST;
}
