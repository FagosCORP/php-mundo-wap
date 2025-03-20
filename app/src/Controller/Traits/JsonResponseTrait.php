<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use App\Controller\Enum\HttpStatusCode\ErrorStatus;
use App\Controller\Enum\HttpStatusCode\SuccessStatus;
use Cake\Http\Response;

trait JsonResponseTrait
{
    /**
     * Retorna uma resposta JSON padronizada
     *
     * @param Response $response Instância da response do CakePHP
     * @param ErrorStatus | SuccessStatus $code Código HTTP da resposta
     * @param array $data Dados a serem retornados no corpo da resposta
     * @return Response
     */
    public function jsonResponse(ErrorStatus|SuccessStatus $code, array $data = []): Response
    {
        $data['success'] = $code instanceof SuccessStatus;
        $response = new Response();

        return $response
            ->withStatus($code->value)
            ->withType('application/json')
            ->withStringBody(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
