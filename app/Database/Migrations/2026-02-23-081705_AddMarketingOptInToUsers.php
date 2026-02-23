<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMarketingOptInToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'marketing_opt_in' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'unsubscribe_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'unique'     => true,
            ],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['marketing_opt_in', 'unsubscribe_token']);
    }
}
