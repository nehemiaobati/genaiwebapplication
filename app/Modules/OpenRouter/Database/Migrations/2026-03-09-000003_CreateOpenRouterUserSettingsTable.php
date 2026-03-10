<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the openrouter_user_settings table.
 */
class CreateOpenRouterUserSettingsTable extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'unique' => true],
            'assistant_mode_enabled' => ['type' => 'BOOLEAN', 'default' => true],
            'stream_output_enabled'  => ['type' => 'BOOLEAN', 'default' => false],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('openrouter_user_settings');
    }

    /**
     * @return void
     */
    public function down(): void
    {
        $this->forge->dropTable('openrouter_user_settings');
    }
}
