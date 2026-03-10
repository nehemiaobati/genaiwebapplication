<?php

namespace App\Modules\OpenRouter\Entities;

use CodeIgniter\Entity\Entity;

class OpenRouter extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
}