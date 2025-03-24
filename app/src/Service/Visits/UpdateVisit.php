<?php

declare(strict_types=1);

namespace App\Service\Visits;

use App\Service\Utils\Visit\Calculate;
use App\Model\Entity\Visit;
use App\Model\Table\VisitsTable;
use App\Service\Address\UpdateAddress;
use App\Service\Workday\UpdateWorkday;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class UpdateVisit
{
    private VisitsTable $visits;
    private UpdateAddress $updateAddress;
    private UpdateWorkday $updateWorkday;

    public function __construct(
        UpdateAddress $updateAddress,
        UpdateWorkday $updateWorkday
    ) {
        $this->visits = TableRegistry::getTableLocator()->get('Visits');
        $this->updateAddress = $updateAddress;
        $this->updateWorkday = $updateWorkday;
    }

    public function execute(int $id, array $data): Visit
    {
        $connection = ConnectionManager::get('default');
        return $connection->transactional(function () use ($id, $data) {

            $visit = $this->visits->get($id);

            $newDate = Hash::get($data, 'date');
            $oldDate = $visit->date->toDateString();

            if (Hash::contains($data, ['forms', 'products'])) {
                $data['duration'] = Calculate::duration(
                    Hash::get($data, 'forms', $visit->forms),
                    Hash::get($data, 'products', $visit->products)
                );
            }

            $this->visits->patchEntity($visit, Hash::remove($data, 'address'));
            $this->visits->saveOrFail($visit);

            $equalDates = $newDate && $newDate == $oldDate;

            if ($equalDates) {
                $this->updateWorkday->execute($oldDate);
            }

            if (! $equalDates) {
                $this->updateWorkday->executeInDates($oldDate, $newDate);
            }

            if (Hash::check($data, 'address')) {
                $data['address']['foreign_table'] = 'visits';
                $this->updateAddress->execute($visit->id, $data['address']);
            }
            $this->visits->loadInto($visit, ['Addresses']);
            return $visit;
        });
    }
}
