<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * option_type:   MC Multiple Choice | TF True/False | ID Identification | MT Matching Type | FB Fill in the Blanks | ES Essay
 * question_type: RM Remembering | UN Understanding | AP Applying | AN Analyzing | EV Evaluating | CR Creating
 * quiz_type:     WW Written Work | ST1 Summative Test 1 | ST2 Summative Test 2 | TE Term Exam
 */
class Quizzes extends Migration
{
    public function up()
    {
        // Questions (bowdetailsid = the competency row in bow_details)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'bowdetailsid' => ['type' => 'INT', 'unsigned' => true],
            'option_type' => ['type' => 'ENUM', 'constraint' => ['MC', 'TF', 'ID', 'MT', 'FB', 'ES']],
            'question_type' => ['type' => 'ENUM', 'constraint' => ['RM', 'UN', 'AP', 'AN', 'EV', 'CR']],
            'description' => ['type' => 'TEXT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('bowdetailsid');
        $this->forge->createTable('questions');

        // Options
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'questionid' => ['type' => 'INT', 'unsigned' => true],
            'description' => ['type' => 'TEXT'],
            'match_text' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'isanswer' => ['type' => 'ENUM', 'constraint' => ['T', 'F'], 'default' => 'F'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('questionid');
        $this->forge->createTable('options');

        // Quizzes
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'quizcode' => ['type' => 'VARCHAR', 'constraint' => 50],
            'sectionid' => ['type' => 'INT', 'unsigned' => true],
            'schoolyearid' => ['type' => 'INT', 'unsigned' => true],
            'term' => ['type' => 'INT'],
            'quiz_type' => ['type' => 'ENUM', 'constraint' => ['WW', 'ST1', 'ST2', 'TE'], 'default' => 'WW'],
            'show_answer' => ['type' => 'ENUM', 'constraint' => ['T', 'F'], 'default' => 'F'],
            'isactive' => ['type' => 'ENUM', 'constraint' => ['T', 'F'], 'default' => 'F'],
            'shuffle_questions' => ['type' => 'ENUM', 'constraint' => ['T', 'F'], 'default' => 'F'],
            'shuffle_options' => ['type' => 'ENUM', 'constraint' => ['T', 'F'], 'default' => 'F'],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('quizcode');
        $this->forge->addKey(['sectionid', 'schoolyearid', 'term']);
        $this->forge->createTable('quizzes');

        // Quiz details (which questions belong to which quiz)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'quizid' => ['type' => 'INT', 'unsigned' => true],
            'questionid' => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['quizid', 'questionid']);
        $this->forge->createTable('quizzes_details');
    }

    public function down()
    {
        $this->forge->dropTable('quizzes_details', true);
        $this->forge->dropTable('quizzes', true);
        $this->forge->dropTable('options', true);
        $this->forge->dropTable('questions', true);
    }
}