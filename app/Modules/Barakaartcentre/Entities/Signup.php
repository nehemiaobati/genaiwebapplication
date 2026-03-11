<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Signup
 * @package App\Modules\Barakaartcentre\Entities
 */
class Signup extends Entity
{
    protected $attributes = [
        'id'         => null,
        'email'      => null,
        'source'     => null,
        'created_at' => null,
        'updated_at' => null,
    ];
    protected $dates = ['created_at', 'updated_at'];
}
