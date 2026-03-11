<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterBarakaEnumsToVarchar extends Migration
{
    public function up(): void
    {
        // Alter artworks category to VARCHAR
        $this->forge->modifyColumn('baraka_artworks', [
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => 'Original',
            ],
        ]);

        // Alter services type to VARCHAR
        $this->forge->modifyColumn('baraka_services', [
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => 'Community',
            ],
        ]);
    }

    public function down(): void
    {
        // Revert artworks category to ENUM
        $this->forge->modifyColumn('baraka_artworks', [
            'category' => [
                'type' => 'ENUM',
                'constraint' => ['Original', 'Student', 'Community'],
                'default' => 'Original',
            ],
        ]);

        // Revert services type to ENUM
        $this->forge->modifyColumn('baraka_services', [
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['Revenue', 'Community'],
                'default' => 'Community',
            ],
        ]);
    }
}
