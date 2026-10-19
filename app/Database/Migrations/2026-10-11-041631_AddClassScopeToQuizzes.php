<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Run AFTER the Quizzes migration.
 *  quizzes.classid     - the class (subject in a section) a quiz belongs to; NULL = admin-created quiz
 *  questions.createdby - users.id of the teacher who wrote the question (teachers edit only their own)
 *
 * If you have not run Quizzes yet you can fold these two columns into it instead.
 */
class AddClassScopeToQuizzes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('quizzes', [
            'classid' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'quizcode'],
        ]);
        $this->db->query('ALTER TABLE `quizzes` ADD INDEX `idx_quizzes_classid` (`classid`)');

        $this->forge->addColumn('questions', [
            'createdby' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('questions', 'createdby');
        $this->db->query('ALTER TABLE `quizzes` DROP INDEX `idx_quizzes_classid`');
        $this->forge->dropColumn('quizzes', 'classid');
    }
}