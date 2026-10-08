<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AttendanceTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
                'unsigned' => true,
                'null' => false
            ],
            'studentid' => [
                'type' => 'INT',
                'null' => false,
                'unsigned' => true
            ],
            'sectionid' => [
                'type' => 'INT',
                'null' => false,
                'unsigned' => true
            ],
            'schoolyearid' => [
                'type' => 'INT',
                'null' => false,
                'unsigned' => true
            ],
            'classdate' => [
                'type' => 'DATE'
            ],
            'classtime' => [
                'type' => 'TIME'
            ],
            'attendancestatus' => [
                'type' => 'ENUM',
                'constraint' => ['P', 'A', 'L']
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('attendances');
    }

    public function down()
    {
        $this->forge->dropTable('attendances');

    }
}
