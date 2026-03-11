<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Models;

use CodeIgniter\Model;
use App\Modules\Barakaartcentre\Entities\Artwork;

/**
 * Class ArtworkModel
 * @package App\Modules\Barakaartcentre\Models
 */
class ArtworkModel extends Model
{
    protected $table            = 'baraka_artworks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Artwork::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title', 'category', 'image_path', 'description', 'price', 'is_sold'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
