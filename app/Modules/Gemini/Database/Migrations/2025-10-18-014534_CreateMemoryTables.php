<?php

declare(strict_types=1);

namespace App\Modules\Gemini\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Class CreateMemoryTables
 *
 * @package App\Modules\Gemini\Database\Migrations
 * @author  Senior Developer
 * @since   1.0.0
 */
class CreateMemoryTables extends Migration
{
    public function up(): void
    {
        // --- Interactions Table ---
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'unique_id' => ['type' => 'VARCHAR', 'constraint' => 255],
            'timestamp' => ['type' => 'DATETIME'],
            'user_input_raw' => ['type' => 'TEXT'],
            'ai_output' => ['type' => 'TEXT'],
            'ai_output_raw' => ['type' => 'TEXT', 'null' => true],
            'relevance_score' => ['type' => 'DECIMAL', 'constraint' => '10,4', 'default' => 1.0],
            'last_accessed' => ['type' => 'DATETIME'],
            'context_used_ids' => ['type' => 'JSON', 'null' => true],
            'embedding' => ['type' => 'JSON', 'null' => true],
            'keywords' => ['type' => 'JSON', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('unique_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('timestamp');
        $this->forge->addKey('created_at');
        $this->forge->createTable('interactions', true, [
            'ENGINE'          => 'InnoDB',
            'CHARACTER SET'   => 'utf8mb4',
            'COLLATE'         => 'utf8mb4_unicode_ci',
            'ROW_FORMAT'      => 'DYNAMIC',
        ]);

        // --- Entities Table ---
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'entity_key' => ['type' => 'VARCHAR', 'constraint' => 255],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'type' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Concept'],
            'access_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'relevance_score' => ['type' => 'DECIMAL', 'constraint' => '10,4', 'default' => 1.0],
            'mentioned_in' => ['type' => 'JSON', 'null' => true],
            'relationships' => ['type' => 'JSON', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['user_id', 'entity_key']);
        $this->forge->addKey('type');
        $this->forge->addKey('created_at');
        $this->forge->createTable('entities', true, [
            'ENGINE'          => 'InnoDB',
            'CHARACTER SET'   => 'utf8mb4',
            'COLLATE'         => 'utf8mb4_unicode_ci',
            'ROW_FORMAT'      => 'DYNAMIC',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('interactions', true);
        $this->forge->dropTable('entities', true);
    }
}
