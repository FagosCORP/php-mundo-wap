<?php

declare(strict_types=1);

namespace App\Exceptions;

use Cake\Http\Exception\HttpException;

class AllocationException extends HttpException
{
    protected $_defaultCode = 409;
    protected $message = 'Todas visitas já estão alocadas nessa data.';
}
