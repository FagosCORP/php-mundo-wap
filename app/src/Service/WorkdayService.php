<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Table\VisitsTable;
use App\Model\Table\WorkdaysTable;
use Cake\Chronos\Chronos;
use Cake\ORM\TableRegistry;

class WorkdayService
{
    protected WorkdaysTable $workdays;
    protected VisitsTable $visits;
    protected ?VisitService $visitsService;
    const DAILYLIMIT = 480; //minutes limit

    public function __construct(?VisitService $visitsService = null)
    {
        $this->visits = TableRegistry::getTableLocator()->get('Visits');
        $this->workdays = TableRegistry::getTableLocator()->get('Workdays');
        $this->visitsService = $visitsService;
    }

    public function notHasLimitDaily(string $date, ?int $duration, bool $isCompleted = true): bool
    {
        if (!$isCompleted) {
            return false;
        }

        $workday = $this->getByDate($date);
        $totalDuration = ($workday->duration ?? 0) + $duration;

        return $totalDuration >= self::DAILYLIMIT;
    }

    public function updateWorkday(string $newDate): void
    {
        $this->refreshWorkdayStats($newDate);
    }

    public function updateWorkdayDates(string $oldDate, string $newDate): void
    {
        $this->refreshWorkdayStats($oldDate);
        $this->refreshWorkdayStats($newDate);
    }

    private function createOrUpdateByDate(string $date): ?object
    {
        return $this->workdays->findOrCreate(
            ['date' => $date],
            function ($entity) use ($date) {
                $entity->date = $date;
            }
        );
    }

    private function refreshWorkdayStats(string $date): void
    {
        $date = $this->normalizeDate($date);
        $workday = $this->createOrUpdateByDate($date);

        $workday->visits = $this->countTotalVisits($date);
        $workday->completed = $this->countCompletedVisits($date);
        $workday->duration = $this->calculateTotalDuration($date);

        $this->workdays->saveOrFail($workday);
    }

    private function normalizeDate(string $date): string
    {
        return Chronos::parse($date)->toDateString();
    }

    private function countTotalVisits(string $date)
    {
        return $this->visits->find()->where(['date' => $date])->count();
    }

    private function countCompletedVisits(string $date)
    {
        return $this->visits->find()->where(['date' => $date, 'completed' => 1])->count();
    }

    private function calculateTotalDuration(string $date)
    {
        return $this->visits->find()
            ->select(['total' => 'SUM(duration)'])
            ->where(['date' => $date, 'completed' => 1])
            ->first()
            ->total ?? 0;
    }

    public function getAll()
    {
        return $this->workdays->find()->all();
    }

    public function getByDate(string $date)
    {
        return $this->workdays->find()->where(['date' => $this->normalizeDate($date)])->first();
    }
}
