<?php

namespace App\Service\Provider;

interface ICepGatewayStrategy
{
    public function fetch(string $cep): ?array;
    public function normalize(array $data): array;
}
