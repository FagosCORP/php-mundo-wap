<?php

declare(strict_types=1);

namespace App\Service;

use App\Exceptions\LimitHoursDailyException;
use Cake\Chronos\Chronos;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Exception;

class VisitService
{
    private $visits;
    private $addressService;
    private $workdayService;

    public function __construct(WorkdayService $workdayService)
    {
        $this->visits = TableRegistry::getTableLocator()->get('Visits');
        $this->addressService = new AddressService();
        $this->workdayService = $workdayService;
    }

    private function calculateDuration(int $forms, int $products): int
    {
        return ($forms * 15) + ($products * 5);
    }

    private function validateDailyLimit(string $date, int $duration, bool $completed)
    {
        if ($this->workdayService->notHasLimitDaily($date, $duration, $completed)) {
            throw new LimitHoursDailyException();
        }
    }

    public function create(array $data)
    {
        $connection = ConnectionManager::get('default');

        return $connection->transactional(function () use ($data) {
            $visitData = Hash::remove($data, 'address');
            $visitData['duration'] = $this->calculateDuration($data['forms'] ?? 0, $data['products'] ?? 0);

            $this->validateDailyLimit($data['date'], $visitData['duration'], (bool) $visitData['completed']);

            $visit = $this->visits->newEntity($visitData);
            $this->visits->saveOrFail($visit);

            if (!empty($data['address'])) {
                $this->addressService->createAddress($visit->id, $data['address']);
            }

            $this->workdayService->updateWorkday($data['date']);
            return $visit;
        });
    }

    public function update(int $id, array $data)
    {
        $connection = ConnectionManager::get('default');

        return $connection->transactional(function () use ($id, $data) {
            $visit = $this->visits->get($id);
            $newDate = Hash::get($data, 'date');
            $oldDate = $visit->date->toDateString();

            if (isset($data['forms']) || isset($data['products'])) {
                $data['duration'] = $this->calculateDuration(
                    Hash::get($data, 'forms', $visit->forms),
                    Hash::get($data, 'products', $visit->products)
                );
            }

            $this->validateDailyLimit($newDate ?? $oldDate, $data['duration'], (bool) $visit->completed);
            $this->visits->patchEntity($visit, Hash::remove($data, 'address'));
            $this->visits->saveOrFail($visit);

            if ($newDate && $newDate !== $oldDate) {
                $this->workdayService->updateWorkdayDates($oldDate, $newDate);
            }

            if (!empty($data['address'])) {
                $this->addressService->updateAddress($visit->id, $data['address']);
            }

            $this->workdayService->updateWorkday($newDate ?? $oldDate);
            return $visit;
        });
    }

    public function findAllByDate(string $date)
    {
        return $this->visits->find()
            ->contain(['Addresses'])
            ->where(['DATE(Visits.date)' => Chronos::parse($date)->toDateString()])
            ->all();
    }

    public function redistributeVisits(string $date): array
    {
        $connection = ConnectionManager::get('default');
        return $connection->transactional(function () use ($date) {
            $date = Chronos::parse($date)->toDateString();
            $originalVisits = $this->visits->find()
                ->where(['date' => $date, 'completed' => 0])
                ->order(['date' => 'ASC'])
                ->all();

            if ($originalVisits->isEmpty()) {
                return ['success' => true, 'data' => 'Nenhuma visita pendente para redistribuir'];
            }

            return [
                'rescheduled' => $this->allocateVisits($originalVisits->toArray(), Chronos::parse($date)->addDays(1)),
                'remaining' => count($originalVisits)
            ];
        });
    }

    private function allocateVisits(array $visits, Chronos $startDate): array
    {
        $results = [];

        foreach ($visits as $visit) {
            $availableDate = $this->findNextAvailableDate($visit, $startDate);

            if (!$availableDate) {
                throw new Exception("Não foi possível alocar a visita {$visit->id} em 30 dias.");
            }

            $visit->date = $availableDate;
            $this->visits->save($visit);
            $results[] = ['id' => $visit->id, 'new_date' => $availableDate->format('Y-m-d')];
        }

        $this->workdayService->updateWorkday($startDate->subDays(1)->toDateString());
        return $results;
    }

    private function findNextAvailableDate($visit, Chronos $startDate): ?Chronos
    {
        $currentDate = clone $startDate;

        $maxAttempts = 30;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $hasLimitDaily = !$this->workdayService->notHasLimitDaily($currentDate->toDateString(), $visit->duration);
            if ($hasLimitDaily) {
                return $currentDate;
            }
            $currentDate = $currentDate->addDays(1);
        }

        return null;
    }

    public function countTotalVisits(string $date)
    {
        return $this->visits->find()->where(['date' => $date])->count();
    }

    public function countCompletedVisits(string $date)
    {
        return $this->visits->find()->where(['date' => $date, 'completed' => 1])->count();
    }

    public function calculateTotalDuration(string $date)
    {
        return $this->visits->find()
            ->select(['total' => 'SUM(duration)'])
            ->where(['date' => $date, 'completed' => 1])
            ->first()
            ->total ?? 0;
    }
}
