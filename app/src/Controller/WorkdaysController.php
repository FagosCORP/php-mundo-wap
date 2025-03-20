<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Enum\HttpStatusCode\ErrorStatus;
use App\Controller\Enum\HttpStatusCode\SuccessStatus;
use App\Controller\Traits\JsonResponseTrait;
use App\Service\VisitService;
use App\Service\WorkdayService;
use Exception;

class WorkdaysController extends AppController
{

    use JsonResponseTrait;

    protected WorkdayService $workdayService;


    public function initialize(): void
    {
        parent::initialize();
        $this->workdayService = new WorkdayService();
    }

    public function index()
    {
        try {
            $workdays = $this->workdayService->getAll();
            return $this->jsonResponse(
                SuccessStatus::OK,
                [
                    'data' => $workdays
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

    public function closeDay()
    {
        try {
            $visitService = new VisitService($this->workdayService);

            $date = $this->request->getQuery('date');
            $redestributedVisits = $visitService->redistributeVisits($date);

            return $this->jsonResponse(
                SuccessStatus::CREATED,
                ['data' => $redestributedVisits]
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
