<?php

declare(strict_types=1);

namespace App\Exceptions;

use Cake\Http\Exception\HttpException;

class DatabaseOperationException extends HttpException
{
    protected $_defaultCode = 503;
    protected $message = 'Erro na operação com o banco de dados';
}
