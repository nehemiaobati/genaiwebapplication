<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBarakaTables extends Migration
{
    public function up(): void
    {
        // 1. users Table
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 255, 'unique' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'          => ['type' => 'ENUM', 'constraint' => ['admin', 'client'], 'default' => 'client'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('role');
        $this->forge->createTable('baraka_users', true);

        // 2. artworks Table
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'category'    => ['type' => 'ENUM', 'constraint' => ['Original', 'Student', 'Community'], 'default' => 'Original'],
            'image_path'  => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => 'https://picsum.photos/seed/art/600/600'],
            'description' => ['type' => 'TEXT', 'null' => true],
            'price'       => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'is_sold'     => ['type' => 'BOOLEAN', 'default' => false],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('category');
        $this->forge->addKey('is_sold');
        $this->forge->createTable('baraka_artworks', true);

        // 3. services Table
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'title'             => ['type' => 'VARCHAR', 'constraint' => 255],
            'type'              => ['type' => 'ENUM', 'constraint' => ['Revenue', 'Community'], 'default' => 'Community'],
            'icon_or_image'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'short_description' => ['type' => 'TEXT', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('type');
        $this->forge->createTable('baraka_services', true);

        // 4. workshops Table
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'event_date'  => ['type' => 'DATE'],
            'time'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'fee'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'description' => ['type' => 'TEXT', 'null' => true],
            'image_path'  => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => 'https://picsum.photos/seed/workshop/600/400'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('event_date');
        $this->forge->createTable('baraka_workshops', true);

        // 5. email_signups Table
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 255, 'unique' => true],
            'source'     => ['type' => 'ENUM', 'constraint' => ['newsletter', 'workshop'], 'default' => 'newsletter'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('source');
        $this->forge->createTable('baraka_email_signups', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('baraka_email_signups', true);
        $this->forge->dropTable('baraka_workshops', true);
        $this->forge->dropTable('baraka_services', true);
        $this->forge->dropTable('baraka_artworks', true);
        $this->forge->dropTable('baraka_users', true);
    }
}
