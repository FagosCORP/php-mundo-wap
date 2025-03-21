<?php

namespace App\Controller\Traits;

use App\Controller\Enum\HttpStatusCode\HttpStatusCode;
use App\Exceptions\CepNotFoundException;
use App\Exceptions\LimitHoursDailyException;
use Cake\Http\Response;
use Cake\Validation\Validator;

trait ApiResponseTrait
{
    public function jsonResponse(int $code, array $data): Response
    {
        $response = new Response();
        return $response
            ->withStatus($code)
            ->withType('application/json')
            ->withStringBody(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function successResponse(mixed $data, HttpStatusCode $status = HttpStatusCode::OK): Response
    {
        return $this->jsonResponse($status->value, ['data' => $data]);
    }

    public function validationErrorResponse(array $errors): Response
    {
        return $this->jsonResponse(HttpStatusCode::BAD_REQUEST->value, ['errors' => $errors]);
    }

    public function errorResponse(string $message, HttpStatusCode $status = HttpStatusCode::BAD_REQUEST): Response
    {
        return $this->jsonResponse($status->value, ['error' => $message]);
    }

    public function handleServiceCall(callable $operation): Response
    {
        try {
            return $this->successResponse($operation());
        } catch (CepNotFoundException | LimitHoursDailyException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (\Throwable $e) {
            return $this->errorResponse('Erro interno do servidor', HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function validateRequest(Validator $validator): array
    {
        $errors = $validator->validate($this->request->getData());
        return $errors;
    }
}

