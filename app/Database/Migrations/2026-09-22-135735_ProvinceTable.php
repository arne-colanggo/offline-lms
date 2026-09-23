<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProvinceTable extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'province_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'region_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
            'province_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false
            ]
        ]);
        $this->forge->addPrimaryKey('province_id');
        $this->forge->createTable('provinces');
    }

    public function down()
    {
        //Drop Table
        $this->forge->dropTable('provinces');
    }
}
