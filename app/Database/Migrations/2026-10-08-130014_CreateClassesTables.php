<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateClassesTables extends Migration
{
    public function up()
    {
        // A class = one subject taught to one section in one school year
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'sectionid' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'subjectid' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'teacherid' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'schoolyearid' => ['type' => 'INT', 'constraint' => 9], // e.g. 2026-2027
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at' => [
                'type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'null' => false,
            ]
        ]);
        $this->forge->addKey('id', true);
        // Same subject can't be created twice for a section in the same school year
        $this->forge->addUniqueKey(['sectionid', 'subjectid', 'schoolyearid']);
        $this->forge->createTable('classes');

        // Students placed in each class
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'classid' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'studentid' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'createdat' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['classid', 'studentid']);
        $this->forge->createTable('class_students');
    }

    public function down()
    {
        $this->forge->dropTable('class_students', true);
        $this->forge->dropTable('classes', true);
    }
}
