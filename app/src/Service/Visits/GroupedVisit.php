<?php

declare(strict_types=1);

namespace App\Service\Visits;

use App\Model\Table\VisitsTable;
use Cake\ORM\TableRegistry;

class GroupedVisit
{
    private VisitsTable $visits;

    public function __construct()
    {
        $this->visits = TableRegistry::getTableLocator()->get('Visits');
    }

    public function totalCount(string $date): ?int
    {
        return $this->visits->find()->where(['date' => $date])->count();
    }

    public function completedCount(string $date): int
    {
        return $this->visits->find()->where(['date' => $date, 'completed' => 1])->count();
    }

    public function completedDuration(string $date): int
    {
        return (int)$this->visits->find()
            ->select(['total' => 'SUM(duration)'])
            ->where(['date' => $date, 'completed' => 1])
            ->first()
            ->total ?? 0;
    }
}
