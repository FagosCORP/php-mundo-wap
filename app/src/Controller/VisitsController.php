<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Enum\HttpStatusCode\ErrorStatus;
use App\Controller\Enum\HttpStatusCode\SuccessStatus;
use App\Controller\Traits\JsonResponseTrait;
use App\Exceptions\CepNotFoundException;
use App\Service\VisitService;
use App\Service\WorkdayService;
use Cake\Validation\Validator;
use Exception;

class VisitsController extends AppController
{
    use JsonResponseTrait;

    protected VisitService $visitService;

    public function initialize(): void
    {
        parent::initialize();
        $workdayService = new WorkdayService();
        $this->visitService = new VisitService($workdayService);
    }

    public function byDate()
    {
        $date = $this->request->getQuery('date');
        $visits = $this->visitService->findAllByDate($date);

        return $this->jsonResponse(
            SuccessStatus::OK,
            [
                'data' => $visits
            ]
        );
    }

    public function add()
    {

        $data = $this->request->getData();

        $validator = new Validator();
        $validator = $this->Visits->validationRequest($validator);

        $errors = $validator->validate($data);

        if (!empty($errors)) {
            return $this->jsonResponse(
                ErrorStatus::BAD_REQUEST,
                [
                    'errors' => $errors
                ]
            );
        }

        try {
            $visit = $this->visitService->create($data);
            return $this->jsonResponse(
                SuccessStatus::OK,
                [
                    'data' => $visit
                ]
            );
        } catch (CepNotFoundException $e) {
            return $this->jsonResponse(
                ErrorStatus::BAD_REQUEST,
                [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]
            );
        } catch (Exception $e) {
            return $this->jsonResponse(
                ErrorStatus::BAD_REQUEST,
                [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]
            );
        };
    }

    public function edit(int $id)
    {

        $data = $this->request->getData();

        $validator = new Validator();
        $validator = $this->Visits->validationRequest($validator);

        $errors = $validator->validate($data);

        if (!empty($errors)) {
            return $this->jsonResponse(
                ErrorStatus::BAD_REQUEST,
                [
                    'errors' => $errors
                ]
            );
        }

        try {
            $visit = $this->visitService->update($id, $data);
            return $this->jsonResponse(
                SuccessStatus::CREATED,
                [
                    'data' => $visit
                ]
            );
        } catch (CepNotFoundException $e) {
            return $this->jsonResponse(
                ErrorStatus::BAD_REQUEST,
                [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]
            );
        } catch (Exception $e) {
            return $this->jsonResponse(
                ErrorStatus::BAD_REQUEST,
                [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]
            );
        };
    }
}
