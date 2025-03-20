<?php

namespace App\Service\Provider;

class CepProvider
{
    private array $strategies;

    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    public function fetchCepData(string $cep): ?array
    {
        foreach ($this->strategies as $strategy) {
            try {
                $data = $strategy->fetch($cep);
                if ($data) {
                    return $strategy->normalize($data);
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return null;
    }
}
