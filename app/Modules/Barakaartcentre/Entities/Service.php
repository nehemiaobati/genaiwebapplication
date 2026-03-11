<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Service
 * @package App\Modules\Barakaartcentre\Entities
 */
class Service extends Entity
{
    protected $attributes = [
        'id'                => null,
        'title'             => null,
        'type'              => null,
        'icon_or_image'     => null,
        'short_description' => null,
        'created_at'        => null,
        'updated_at'        => null,
    ];
    protected $dates = ['created_at', 'updated_at'];
}
