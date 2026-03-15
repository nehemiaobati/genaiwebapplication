<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Order
 * Entity representing a purchase order.
 * 
 * @property int $id
 * @property string $order_reference
 * @property string $phone_number
 * @property string $name
 * @property string $email
 * @property string $item_type
 * @property int $item_id
 * @property float $amount
 * @property string|null $delivery_address
 * @property string $status
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class Order extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'      => 'integer',
        'item_id' => 'integer',
        'amount'  => 'float',
        'is_resolved' => 'boolean',
    ];
}
