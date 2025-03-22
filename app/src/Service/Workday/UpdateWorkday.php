<?php

declare(strict_types=1);

namespace App\Service\Workday;

use App\Model\Entity\Workday;
use App\Model\Table\WorkdaysTable;
use App\Service\Visits\GroupedVisit;
use Cake\Chronos\Chronos;
use Cake\ORM\TableRegistry;

class UpdateWorkday
{
    protected WorkdaysTable $workdays;
    protected GroupedVisit $groupedVisit;

    const DAILYLIMIT = 480; //minutes limit

    public function __construct(GroupedVisit $groupedVisit)
    {
        $this->workdays = TableRegistry::getTableLocator()->get('Workdays');
        $this->groupedVisit = $groupedVisit;
    }

    private function normalizeDate(string $date): string
    {
        return Chronos::parse($date)->toDateString();
    }

    public function execute(string $newDate): void
    {
        $this->syncWorkDay($newDate);
    }

    public function executeInDates(string $oldDate, string $newDate): void
    {
        $this->syncWorkDay($oldDate);
        $this->syncWorkDay($newDate);
    }

    private function defineEntity(string $date): Workday
    {
        // disclaimer -> aqui ele define ou cria no banco
        // retornando a entity
        //
        return $this->workdays->findOrCreate(
            ['date' => $date],
            function ($entity) use ($date) {
                $entity->date = $date;
            },
            [
                'accessibleFields' => ['duration' => true]
            ]
        );
    }
    public function executeOnlyVisits(string $date): void
    {
        $date = $this->normalizeDate($date);
        $completeCount = $this->groupedVisit->completedCount($date);

        if (!$completeCount) {
            $this->workdays->deleteAll(['date' => $date]);
        }

        $workday = $this->defineEntity($date);
        $workday->visits = $this->groupedVisit->totalCount($date);
        $this->workdays->saveOrFail($workday);
    }

    private function syncWorkDay(string $date): void
    {
        $date = $this->normalizeDate($date);
        $completeCount = $this->groupedVisit->completedCount($date);

        if (!$completeCount) {
            $this->workdays->deleteAll(['date' => $date]);
            return;
        }

        $workday = $this->defineEntity($date);
        $workday->visits = $this->groupedVisit->totalCount($date);
        $workday->completed = $completeCount;
        $workday->duration = $this->groupedVisit->completedDuration($date);

        $this->workdays->saveOrFail($workday);
    }
}
