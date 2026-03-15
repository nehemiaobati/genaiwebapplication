<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateBarakaOrders
 * Creates the baraka_orders table for tracking purchase attempts and success status.
 */
class CreateBarakaOrders extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'order_reference' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'item_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'delivery_address' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('phone_number');
        $this->forge->addKey('item_type');
        
        $this->forge->createTable('baraka_orders');
    }

    public function down()
    {
        $this->forge->dropTable('baraka_orders');
    }
}
