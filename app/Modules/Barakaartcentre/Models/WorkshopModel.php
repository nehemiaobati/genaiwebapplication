<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Models;

use CodeIgniter\Model;
use App\Modules\Barakaartcentre\Entities\Workshop;

/**
 * Class WorkshopModel
 * @package App\Modules\Barakaartcentre\Models
 */
class WorkshopModel extends Model
{
    protected $table            = 'baraka_workshops';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Workshop::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title', 'event_date', 'time', 'fee', 'description', 'image_path'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
