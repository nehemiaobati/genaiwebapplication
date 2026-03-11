<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Models;

use CodeIgniter\Model;
use App\Modules\Barakaartcentre\Entities\Signup;

/**
 * Class SignupModel
 * @package App\Modules\Barakaartcentre\Models
 */
class SignupModel extends Model
{
    protected $table            = 'baraka_email_signups';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Signup::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'email', 'source'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
