<?php

declare(strict_types=1);

namespace App\Service\Visits;

use App\Model\Entity\Visit;
use App\Model\Table\VisitsTable;
use App\Service\Workday\LimitWorkday;
use App\Service\Workday\UpdateWorkday;
use Cake\Chronos\Chronos;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;
use Exception;

class RemaningVisit
{
    private VisitsTable $visits;
    private UpdateWorkday $updateWorkday;
    private LimitWorkday $limitWorkday;

    public function __construct(
        UpdateWorkday $updateWorkday,
        LimitWorkday $limitWorkday
    ) {
        $this->visits = TableRegistry::getTableLocator()->get('Visits');
        $this->updateWorkday = $updateWorkday;
        $this->limitWorkday = $limitWorkday;
    }

    public function findPendingVisits(string $date): ResultSet
    {
        return $this->visits->find()
            ->where(['date' => $date, 'completed' => 0])
            ->order(['date' => 'ASC'])
            ->all();
    }

    public function excute(string $date): array
    {
        $connection = ConnectionManager::get('default');
        return $connection->transactional(function () use ($date) {

            $date = Chronos::parse($date)->toDateString();
            $pendingVisits = $this->findPendingVisits($date);

            if ($pendingVisits->isEmpty()) {
                return ['Nenhuma visita pendente para redistribuir'];
            }

            return [
                'old_date' => $date,
                'allocate' => $this->allocate(
                    $pendingVisits->toArray(),
                    Chronos::parse($date)
                ),
                'count' => count($pendingVisits)
            ];
        });
    }

    private function allocate(array $visits, Chronos $startDate): array
    {
        $results = [];

        $availableDate = $this->findOpenDate(reset($visits)->duration, $startDate);

        if ($availableDate == $startDate) {
            throw new Exception("Todas visitas já estão alocadas para {$startDate->toDateString()}");
        }

        if (!$availableDate) {
            throw new Exception("Não foi possível alocar a visitas pendentes em 30 dias.");
        }

        foreach ($visits as $visit) {

            $visit->date = $availableDate;
            $this->visits->saveOrFail($visit);
            $results[] = [
                'id' => $visit->id,
                'new_date' => $availableDate->format('Y-m-d')
            ];
        }
        $this->updateWorkday->executeOnlyVisits($startDate->toDateString());
        return $results;
    }

    private function findOpenDate(int $duration, Chronos $date): ?Chronos
    {
        $maxAttempts = 120;
        $accDate = clone $date;
        $i = 0;
        do {
            if ($this->limitWorkday->have($accDate->toDateString(), $duration)) {
                return $accDate;
            }
            $accDate = $accDate->addDays(1);
            $i++;
        } while ($i < $maxAttempts);

        return null;
    }
}
