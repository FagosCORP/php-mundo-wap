<?php

declare(strict_types=1);

namespace App\Service;

use App\Exceptions\CepNotFoundException;
use App\Model\Table\AddressesTable;
use App\Service\Provider\CepProvider;
use App\Service\Provider\RepublicaVirtualStrategy;
use App\Service\Provider\ViaCepStrategy;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class AddressService
{
    protected AddressesTable $addresses;

    public function __construct()
    {
        $this->addresses = TableRegistry::getTableLocator()->get('Addresses');
    }

    public function getAll()
    {
        return $this->addresses->find()->all();
    }

    public function getById(int $id)
    {
        return $this->addresses->get($id);
    }

    public function updateAddress(int $visitId, array $addressData): void
    {
        $this->addresses->deleteAll(['foreign_id' => $visitId, 'foreign_table' => 'visits']);

        $addressData = Hash::merge($addressData, [
            'foreign_id' => $visitId,
            'foreign_table' => 'visits'
        ]);

        $this->create($addressData);
    }

    private function fetchCepData(string $cep)
    {
        $strategies = [
            new RepublicaVirtualStrategy(),
            new ViaCepStrategy()
        ];

        $cepProvider = new CepProvider($strategies);
        return $cepProvider->fetchCepData($cep);
    }

    public function appendCepData(&$data)
    {
        if (!isset($data['postal_code'])) {
            return;
        }

        $cepData = $this->fetchCepData($data['postal_code']);

        if (!$cepData) {
            throw new CepNotFoundException();
        }

        $fields = ['sublocality', 'street', 'city', 'state'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = $cepData[$field] ?? null;
            }
        }
    }

    public function create(array $data)
    {
        $this->appendCepData($data);

        $address = $this->addresses->newEntity($data);

        return $this->addresses->saveOrFail($address);
    }
}
