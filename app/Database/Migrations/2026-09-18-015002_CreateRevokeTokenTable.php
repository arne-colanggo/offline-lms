<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRevokeTokenTable extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'jti' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],

            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'expires_at' => [
                'type' => 'DATETIME',
            ],

            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        // Primary Key
        $this->forge->addKey('id', true);

        // Indexes
        $this->forge->addKey('jti');
        $this->forge->addKey('user_id');
        $this->forge->addKey('expires_at');

        // Create table
        $this->forge->createTable('revoked_tokens');
    }

    public function down()
    {
        //Drop Table revoked_tokens
        $this->forge->dropTable('revoked_tokens');
    }
}
