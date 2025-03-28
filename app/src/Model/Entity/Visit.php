<?php

declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\FrozenDate;
use Cake\ORM\Entity;

/**
 * Visit Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenDate $date
 * @property bool $completed
 * @property int $forms
 * @property int $products
 * @property int $duration
 * @property int $address_id
 *
 * @property \App\Model\Entity\Address $address
 */
class Visit extends Entity
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
        'completed' => true,
        'forms' => true,
        'products' => true,
        'duration' => false,
        'address_id' => true,
        'address' => true,
    ];
}
