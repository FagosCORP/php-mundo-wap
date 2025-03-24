<?php

namespace App\Provider\Address;

interface ICepGatewayStrategy
{
    public function fetch(string $cep): ?array;
    public function normalize(array $data): array;
}
