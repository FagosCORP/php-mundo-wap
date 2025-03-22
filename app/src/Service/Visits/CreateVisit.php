<?php

declare(strict_types=1);

namespace App\Service\Visits;

use App\Model\Entity\Visit;
use App\Model\Table\VisitsTable;
use App\Service\Address\CreateAddress;
use App\Service\Utils\Visit\Calculate;
use App\Service\Workday\UpdateWorkday;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class CreateVisit
{
    private VisitsTable $visits;
    private CreateAddress $createAddress;
    private UpdateWorkday $updateWorkday;

    public function __construct(
        CreateAddress $createAddress,
        UpdateWorkday $updateWorkday
    ) {
        $this->visits = TableRegistry::getTableLocator()->get('Visits');
        $this->createAddress = $createAddress;
        $this->updateWorkday = $updateWorkday;
    }

    public function execute(array $data): Visit
    {
        $connection = ConnectionManager::get('default');

        return $connection->transactional(function () use ($data) {
            $visitData = Hash::remove($data, 'address');
            $visitData['duration'] = Calculate::duration(
                $data['forms'] ?? 0,
                $data['products'] ?? 0
            );

            $visit = $this->visits->newEntity(
                $visitData,
                [
                    'accessibleFields' => ['duration' => true]
                ]
            );
            $this->visits->saveOrFail($visit);

            if (Hash::check($data, 'address')) {
                $data['address']['foreign_table'] = 'visits';
                $this->createAddress->execute($visit->id, $data['address']);
            }

            $this->updateWorkday->execute($data['date']);
            return $visit;
        });
    }
}
