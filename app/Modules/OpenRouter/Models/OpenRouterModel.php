<?php

namespace App\Modules\OpenRouter\Models;

use CodeIgniter\Model;
use App\Modules\OpenRouter\Entities\OpenRouter;

class OpenRouterModel extends Model
{
    protected $table            = 'openrouter_table'; // TODO: Update table name
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = OpenRouter::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}