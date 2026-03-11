<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class User
 * @package App\Modules\Barakaartcentre\Entities
 */
class User extends Entity
{
    protected $attributes = [
        'id'            => null,
        'name'          => null,
        'email'         => null,
        'password_hash' => null,
        'role'          => null,
        'created_at'    => null,
        'updated_at'    => null,
    ];
    protected $dates = ['created_at', 'updated_at'];
}
