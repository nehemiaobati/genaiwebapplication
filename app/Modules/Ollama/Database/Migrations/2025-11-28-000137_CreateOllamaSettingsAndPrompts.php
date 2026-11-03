<?php

declare(strict_types=1);

namespace App\Modules\Ollama\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration for creating ollama_prompts and ollama_user_settings tables.
 *
 * @package App\Modules\Ollama\Database\Migrations
 * @author  Senior Developer
 * @since   1.0.0
 */
class CreateOllamaSettingsAndPrompts extends Migration
{
    public function up(): void
    {
        // Table: ollama_prompts
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'prompt_text' => ['type' => 'TEXT'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ollama_prompts', true, [
            'ENGINE'        => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE'       => 'utf8mb4_unicode_ci',
            'ROW_FORMAT'    => 'DYNAMIC',
        ]);

        // Table: ollama_user_settings
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
        $this->forge->createTable('ollama_user_settings', true, [
            'ENGINE'        => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE'       => 'utf8mb4_unicode_ci',
            'ROW_FORMAT'    => 'DYNAMIC',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('ollama_user_settings', true);
        $this->forge->dropTable('ollama_prompts', true);
    }
}
