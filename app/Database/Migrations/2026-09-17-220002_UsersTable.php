<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class UsersTable extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'unique' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => false,
                'unique' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false
            ],
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'teacher', 'student'],
                'default' => 'teacher'
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1
            ],
            'profile_id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'null' => false
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
        $this->forge->addForeignKey('profile_id', 'profiles', 'id');
        $this->forge->createTable('users', true);
    }

    public function down()
    {
        //
        $this->forge->dropTable('users');
    }
}
