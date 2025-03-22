<?php

declare(strict_types=1);

namespace App\Service\Address;

use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

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
        $addressesTable->deleteAll([
            'foreign_id' => $visitId,
            'foreign_table' => Hash::get($addressData, 'foreign_table', '')
        ]);

        $this->createAddress->execute($visitId, $addressData);
    }
}
