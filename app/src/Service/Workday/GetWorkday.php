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
            ->contain([
                'Visit' => 'Addresses'
            ])
            ->all();
    }
}
