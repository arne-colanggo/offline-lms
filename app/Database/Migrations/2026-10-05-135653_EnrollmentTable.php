<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;
class EnrollmentTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false
            ],
            'sectionid' => [
                'type' => 'INT',
                'constraint' => 10,
                'null' => false
            ],
            'schoolyearid' => [
                'type' => 'INT',
                'constraint' => 5,
                'null' => false
            ],
            'studentid' => [
                'type' => 'INT',
                'constraint' => 10,
                'null' => false
            ],
            'dateenrolled' => [
                'type' => 'DATE'
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
        $this->forge->createTable('enrollments');
    }

    public function down()
    {
        $this->forge->dropTable('enrollments');
    }
}
