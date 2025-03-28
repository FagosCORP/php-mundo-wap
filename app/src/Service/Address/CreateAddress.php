<?php

declare(strict_types=1);

namespace App\Service\Address;

use App\Exceptions\CepNotFoundException;
use App\Model\Entity\Address;
use App\Provider\Address\CepProvider;
use App\Provider\Address\RepublicaVirtualStrategy;
use App\Provider\Address\ViaCepStrategy;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class CreateAddress
{
    private function fetchCepData(string $cep): array
    {
        $strategies = [
            new RepublicaVirtualStrategy(),
            new ViaCepStrategy()
        ];

        $cepProvider = new CepProvider($strategies);
        return $cepProvider->fetchCepData($cep) ?? [];
    }

    public function appendCepData(array &$data): void
    {
        $cepData = $this->fetchCepData($data['postal_code']);

        if (!$cepData) {
            throw new CepNotFoundException('Cep não encontrado.');
        }

        $fields = ['sublocality', 'street', 'city', 'state'];

        foreach ($fields as $field) {
            if (!Hash::check($data, $field)) {
                $data[$field] = $cepData[$field];
            }
        }
    }

    public function execute(int $visitId, array $data): Address
    {
        $addressesTable = TableRegistry::getTableLocator()->get('Addresses');

        $this->appendCepData($data);
        $data = Hash::merge($data, [
            'foreign_id' => $visitId,
            'foreign_table' => Hash::get($data, 'foreign_table', '')
        ]);
        $address = $addressesTable->newEntity($data, [
            'accessibleFields' => [
                'foreign_id' => true,
                'foreign_table' => true,
            ]
        ]);

        return $addressesTable->saveOrFail($address);
    }
}
