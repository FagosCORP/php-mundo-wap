<?php

namespace App\Exceptions;

use App\Controller\Enum\HttpStatusCode\ErrorStatus;
use Exception;

class LimitHoursDailyException extends Exception
{
    protected $message = 'Limite de horas atingidos.';
    protected $code = ErrorStatus::BAD_REQUEST;
}
