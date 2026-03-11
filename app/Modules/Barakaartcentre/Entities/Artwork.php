<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Artwork
 * @package App\Modules\Barakaartcentre\Entities
 */
class Artwork extends Entity
{
    protected $attributes = [
        'id'          => null,
        'title'       => null,
        'category'    => null,
        'image_path'  => null,
        'description' => null,
        'price'       => null,
        'is_sold'     => null,
        'created_at'  => null,
        'updated_at'  => null,
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'is_sold' => 'boolean',
        'price'   => 'float',
    ];
}
