<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Models;

use CodeIgniter\Model;
use App\Modules\Barakaartcentre\Entities\Order;

/**
 * Class OrderModel
 * Handles database operations for the baraka_orders table.
 */
class OrderModel extends Model
{
    protected $table            = 'baraka_orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Order::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'order_reference',
        'phone_number',
        'name',
        'email',
        'item_type',
        'item_id',
        'amount',
        'delivery_address',
        'status',
        'is_resolved'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'order_reference' => 'required|is_unique[baraka_orders.order_reference]',
        'phone_number'    => 'required|min_length[10]',
        'name'           => 'required|min_length[3]',
        'email'          => 'required|valid_email',
        'item_type'      => 'required',
        'item_id'        => 'required|is_natural_no_zero',
        'amount'         => 'required|decimal',
        'status'         => 'required'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
