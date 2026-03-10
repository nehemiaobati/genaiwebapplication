<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the openrouter_prompts table for user-saved prompt templates.
 */
class CreateOpenRouterPromptsTable extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
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
        $this->forge->createTable('openrouter_prompts');
    }

    /**
     * @return void
     */
    public function down(): void
    {
        $this->forge->dropTable('openrouter_prompts');
    }
}
