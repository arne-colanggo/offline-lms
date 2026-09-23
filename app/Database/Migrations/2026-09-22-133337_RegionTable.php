<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RegionTable extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'region_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
                'auto_increment' => true
            ],
            'region_name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'region_description' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false
            ]
        ]);
        $this->forge->addPrimaryKey('region_id');
        $this->forge->createTable('regions');
    }

    public function down()
    {
        //
        $this->forge->dropTable('regions');
    }
}
