<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;
class SubjectTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'grade_level_id' => [
                'type' => 'INT',
                'constraint' => 5
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => new RawSql('CURRENT_TIMESTAMP')
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'null' => false,
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('subjects');
    }

    public function down()
    {
        $this->forge->dropTable('subjects');
    }
}
