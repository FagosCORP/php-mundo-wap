<?php

declare(strict_types=1);

namespace App\Service\Workday;

use App\Model\Table\WorkdaysTable;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;

class GetWorkday
{
    protected WorkdaysTable $workdays;

    public function __construct()
    {
        $this->workdays = TableRegistry::getTableLocator()->get('Workdays');
    }

    public function getAll(): ResultSet
    {
        return $this->workdays->find()
            ->select([
                'Workdays.id',
                'Workdays.date',
                'Workdays.visits',
                'Workdays.completed',
                'Workdays.duration'
            ])
            ->matching('Visits', function ($q) {
                return $q->select([
                    'Visits.id',
                    'Visits.date',
                    'Visits.completed',
                    'Visits.forms',
                    'Visits.products',
                    'Visits.duration'
                ])
                    ->where(['Visits.date = Workdays.date']);
            })->all();
    }
}
