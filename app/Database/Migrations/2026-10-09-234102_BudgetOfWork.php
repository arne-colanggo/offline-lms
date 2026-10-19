<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;
class BudgetOfWork extends Migration
{
    public function up()
    {
        //BOW
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true, 'unsigned' => true],
            'subjectid' => ['type' => 'INT', 'constraint' => 9, 'null' => false, 'unsigned' => true],
            'term' => ['type' => 'INT', 'null' => false, 'constraint' => 4],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'null' => false]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('bows');

        //BOW Details

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true, 'null' => false, 'unsigned' => true],
            'competencycode' => ['type' => 'VARCHAR', 'constraint' => 50],
            'bowid' => ['type' => 'INT', 'null' => false, 'constraint' => 9],
            'learning_objectives' => ['type' => 'TEXT', 'null' => false],
            'week' => ['type' => 'INT', 'constraint' => 4],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('bow_details');

        //Learning Resources

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true, 'unsigned' => true],
            'bowdetailsid' => ['type' => 'INT', 'constraint' => 9],
            'resourcestype' => ['type' => 'ENUM', 'constraint' => ['LP', 'LR']],
            'filetype' => ['type' => 'VARCHAR', 'constraint' => 50],
            'filename' => ['type' => 'VARCHAR', 'constraint' => 50],
            'documentname' => ['type' => 'VARCHAR', 'constraint' => 150],
            'visibletostudents' => ['type' => 'ENUM', 'constraint' => ['T', 'F'], 'default' => 'F']
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('learningresources');
    }

    public function down()
    {
        //
        $this->forge->dropTable('bows');
        $this->forge->dropTable('bow_details');
        $this->forge->dropTable('learningresources');
    }
}