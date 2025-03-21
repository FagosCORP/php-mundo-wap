<?php

declare(strict_types=1);

namespace App\Service\Workday;

use App\Model\Entity\Workday;
use App\Model\Table\WorkdaysTable;
use Cake\Chronos\Chronos;
use Cake\ORM\TableRegistry;

class LimitWorkday
{
    protected WorkdaysTable $workdays;
    const DAILYLIMIT = 480; //minutes limit

    public function __construct()
    {
        $this->workdays = TableRegistry::getTableLocator()->get('Workdays');
    }

    public function have(string $date, ?int $duration): bool
    {
        $workday = $this->getByDate($date);
        $totalDuration = ($workday->duration ?? 0) + $duration;

        return  self::DAILYLIMIT >= $totalDuration;
    }

    private function normalizeDate(string $date): string
    {
        return Chronos::parse($date)->toDateString();
    }

    public function getByDate(string $date): ?Workday
    {
        return $this->workdays->find()->where(['date' => $this->normalizeDate($date)])->first();
    }
}
