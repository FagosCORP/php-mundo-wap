<?php

namespace App\Service\Provider;

use Cake\Http\Client;
use Cake\Utility\Hash;

class RepublicaVirtualStrategy implements ICepGatewayStrategy
{
    public function fetch(string $cep): ?array
    {
        $http = new Client();
        $response = $http->get("https://cep.republicavirtual.com.br/web_cep.php?cep={$cep}&formato=json");

        return $response->isSuccess() && Hash::check($response->getJson(), 'cidade') ? $response->getJson() : null;
    }

    public function normalize(array $data): array
    {
        return [
            'state' => strtoupper($data['uf'] ?? ''),
            'city' => $data['cidade'] ?? '',
            'sublocality' => $data['bairro'] ?? '',
            'street' => trim(($data['tipo_logradouro'] ?? '') . ' ' . ($data['logradouro'] ?? '')),
        ];
    }
}
