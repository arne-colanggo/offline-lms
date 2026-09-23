<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BarangayTable extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'barangay_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
                'auto_increment' => true
            ],
            'municipality_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
            'barangay_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false
            ]
        ]);
        $this->forge->addPrimaryKey('barangay_id');
        $this->forge->createTable('barangays');
    }

    public function down()
    {
        //
        $this->forge->dropTable('barangays');
    }
}
