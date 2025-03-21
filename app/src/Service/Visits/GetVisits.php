<?php

declare(strict_types=1);

namespace App\Service\Visits;

use App\Model\Table\VisitsTable;
use Cake\Chronos\Chronos;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;

class GetVisits
{
    protected VisitsTable $visits;

    public function __construct()
    {
        $this->visits = TableRegistry::getTableLocator()->get('Visits');
    }
    public function findAllByDate(string $date): ?ResultSet
    {
        return $this->visits->find()
            ->contain(['Addresses'])
            ->where(['DATE(Visits.date)' => Chronos::parse($date)->toDateString()])
            ->all();
    }
}
