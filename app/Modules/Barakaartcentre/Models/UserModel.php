<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Models;

use CodeIgniter\Model;
use App\Modules\Barakaartcentre\Entities\User;

/**
 * Class UserModel
 * @package App\Modules\Barakaartcentre\Models
 */
class UserModel extends Model
{
    protected $table            = 'baraka_users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = User::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name', 'email', 'password_hash', 'role'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
