<?php

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Service\Visits\CreateVisit;
use App\Service\Visits\GetVisits;
use App\Service\Visits\UpdateVisit;
use Cake\Http\Response;
use Cake\Validation\Validator;

class VisitsController extends AppController
{
    use ApiResponseTrait;

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
    }

    public function byDate(GetVisits $getVisits): Response
    {
        $date = $this->request->getQuery('date');

        $validator = $this->Visits->validationDateParam(new Validator());
        $errors = $validator->validate(['date' => $date]);

        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        return $this->successResponse($getVisits->findAllByDate($date));
    }

    public function edit(UpdateVisit $updateVisit, int $id): Response
    {
        $validator = $this->Visits->validationRequest(new Validator());
        $errors = $this->validateRequest($validator);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        return $this->handleServiceCall(
            fn() => $updateVisit->execute($id, $this->request->getData())
        );
    }

    public function add(CreateVisit $createVisit): Response
    {
        $validator = $this->Visits->validationRequest(new Validator());
        $errors = $this->validateRequest($validator);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        return $this->handleServiceCall(
            fn() => $createVisit->execute($this->request->getData())
        );
    }
}

