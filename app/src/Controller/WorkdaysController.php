<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Service\Visits\RemaningVisit;
use App\Service\Workday\GetWorkday;
use Cake\Http\Response;
use Cake\Validation\Validator;

class WorkdaysController extends AppController
{
    use ApiResponseTrait;

    public function initialize(): void
    {
        parent::initialize();
    }

    public function index(GetWorkday $getWorkday): Response
    {
        return $this->successResponse($getWorkday->getAll());
    }

    public function closeDay(RemaningVisit $remaningVisit): Response
    {

        $date = $this->request->getQuery('date');

        $validator = $this->Workdays->validationDateParam(new Validator());
        $errors = $validator->validate(['date' => $date]);

        $errors = $this->validateRequest($validator);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        return $this->handleServiceCall(
            fn() => $remaningVisit->excute($date)
        );
    }
}
