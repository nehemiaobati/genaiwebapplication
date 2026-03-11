<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Workshop
 * @package App\Modules\Barakaartcentre\Entities
 */
class Workshop extends Entity
{
    protected $attributes = [
        'id'          => null,
        'title'       => null,
        'event_date'  => null,
        'time'        => null,
        'fee'         => null,
        'description' => null,
        'image_path'  => null,
        'created_at'  => null,
        'updated_at'  => null,
    ];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'fee' => 'float',
    ];
}
