<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class StudentsTable extends Migration
{
    public function up()
    {
        //Students Information

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'null' => false,
                'auto_increment' => true
            ],
            'lrn' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'fathersname' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'mothersname' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'guardiansname' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'relationship' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'parent_guardian_contact' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'remarks' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'profile_id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'null' => false
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('profile_id', 'profiles', 'id');
        $this->forge->createTable('students', true);
    }

    public function down()
    {
        //Drop Table
        $this->forge->dropTable('students');
    }
}
