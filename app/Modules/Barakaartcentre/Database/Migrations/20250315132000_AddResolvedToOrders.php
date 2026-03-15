<?php

namespace App\Modules\Barakaartcentre\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResolvedToOrders extends Migration
{
    public function up()
    {
        $fields = [
            'is_resolved' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'status',
            ],
        ];
        $this->forge->addColumn('baraka_orders', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('baraka_orders', 'is_resolved');
    }
}
