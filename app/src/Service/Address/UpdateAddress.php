<?php

declare(strict_types=1);

namespace App\Service\Address;

use Cake\ORM\TableRegistry;

class UpdateAddress
{
    protected CreateAddress $createAddress;

    public function __construct(
        CreateAddress $createAddress
    ) {
        $this->createAddress = $createAddress;
    }

    public function execute(int $visitId, array $addressData): void
    {
        $addressesTable = TableRegistry::getTableLocator()->get('Addresses');
        $addressesTable->deleteAll(['foreign_id' => $visitId, 'foreign_table' => 'visits']);

        $this->createAddress->execute($visitId, $addressData);
    }
}
