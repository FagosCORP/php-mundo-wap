<?php

declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\FrozenDate;
use Cake\ORM\Entity;

/**
 * Workday Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenDate $date
 * @property int $visits
 * @property int $completed
 * @property int $duration
 */
class Workday extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'date' => true,
        'visits' => false,
        'completed' => false,
        'duration' => false,
    ];
}
