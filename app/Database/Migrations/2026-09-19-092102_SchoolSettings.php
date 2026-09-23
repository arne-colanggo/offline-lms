<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;
class SchoolSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'schoolname' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'schooladdress' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 12,
                'null' => true
            ],
            'schoolhead' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true
            ],
            'schoolheaddesignation' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'logo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'favicon' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'schoolyear' => [
                'type' => 'INT',
                'constraint' => 5,
                'null' => true
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
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
        $this->forge->createTable('settings');

    }

    public function down()
    {
        //Drop Table
        $this->forge->dropTable('settings');
    }
}
