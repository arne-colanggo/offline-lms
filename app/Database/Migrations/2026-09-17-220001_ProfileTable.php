<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class ProfileTable extends Migration
{
    public function up()
    {
        //Personal Profile
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'null' => false,
                'auto_increment' => true,

            ],
            'firstname' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => false,
            ],
            'middlename' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'lastname' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'gender' => [
                'type' => 'ENUM',
                'constraint' => ['M', 'F'],
                'default' => 'M',
                'null' => false
            ],
            'dateofbirth' => [
                'type' => 'date',
                'null' => true
            ],
            'addressid' => [
                'type' => 'INT',
                'constrain' => 5
            ],
            'religion' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'contact' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'fbaccount' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'picture' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'deleted' => [
                'type' => 'ENUM',
                'constraint' => ['F', 'T'],
                'default' => 'F'
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
        $this->forge->addKey('id');
        $this->forge->createTable('profiles', true);
    }

    public function down()
    {
        //drop table profiles
        $this->forge->dropTable('profiles');
    }
}
