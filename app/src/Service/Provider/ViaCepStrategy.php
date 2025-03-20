<?php

namespace App\Service\Provider;

use Cake\Http\Client;
use Cake\Utility\Hash;

class ViaCepStrategy implements ICepGatewayStrategy
{
    public function fetch(string $cep): ?array
    {
        $http = new Client();
        $response = $http->get("https://viacep.com.br/ws/{$cep}/json/");

        return $response->isSuccess() && Hash::check($response->getJson(), 'localidade') ? $response->getJson() : null;
    }

    public function normalize(array $data): array
    {
        return [
            'state' => strtoupper($data['uf'] ?? ''),
            'city' => $data['localidade'] ?? '',
            'sublocality' => $data['bairro'] ?? '',
            'street' => $data['logradouro'] ?? '',
        ];
    }
}
