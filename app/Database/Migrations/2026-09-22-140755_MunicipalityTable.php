<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MunicipalityTable extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'municipality_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'province_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
            'municipality_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false
            ]
        ]);

        $this->forge->addPrimaryKey('municipality_id');
        $this->forge->createTable('municipalities');

    }

    public function down()
    {
        //
        $this->forge->dropTable('municipalities');
    }
}
