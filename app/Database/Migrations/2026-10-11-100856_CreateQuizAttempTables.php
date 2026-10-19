<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Run after Quizzes + AddClassScopeToQuizzes.
 *  quizzes.time_limit      minutes (NULL = no time limit)
 *  quizzes.max_violations  warnings allowed before auto-submit (0 = only log, never auto-submit)
 *  quiz_attempts           one row per student per quiz
 *  quiz_answers            one row per answered question (graded on the server)
 *  quiz_events             anti-cheat / activity log
 */
class CreateQuizAttemptTables extends Migration
{
    public function up()
    {
        $this->forge->addColumn('quizzes', [
            'time_limit' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'max_violations' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 3],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'quizid' => ['type' => 'INT', 'unsigned' => true],
            'studentid' => ['type' => 'INT', 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['in_progress', 'submitted', 'auto_submitted'], 'default' => 'in_progress'],
            'auto_reason' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'question_order' => ['type' => 'TEXT'],
            'option_seed' => ['type' => 'INT', 'unsigned' => true],
            'current_index' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'session_token' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
            'violations' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'last_violation_at' => ['type' => 'DATETIME', 'null' => true],
            'started_at' => ['type' => 'DATETIME'],
            'deadline_at' => ['type' => 'DATETIME', 'null' => true],
            'last_seen_at' => ['type' => 'DATETIME', 'null' => true],
            'submitted_at' => ['type' => 'DATETIME', 'null' => true],
            'score' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['quizid', 'studentid']);
        $this->forge->createTable('quiz_attempts');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'attemptid' => ['type' => 'INT', 'unsigned' => true],
            'questionid' => ['type' => 'INT', 'unsigned' => true],
            'answer' => ['type' => 'MEDIUMTEXT', 'null' => true],   // JSON
            'is_correct' => ['type' => 'TINYINT', 'null' => true],      // NULL = waiting for manual grading
            'score' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'graded_manually' => ['type' => 'TINYINT', 'default' => 0],
            'answered_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['attemptid', 'questionid']);
        $this->forge->createTable('quiz_answers');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'attemptid' => ['type' => 'INT', 'unsigned' => true],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'detail' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('attemptid');
        $this->forge->createTable('quiz_events');
    }

    public function down()
    {
        $this->forge->dropTable('quiz_events', true);
        $this->forge->dropTable('quiz_answers', true);
        $this->forge->dropTable('quiz_attempts', true);
        $this->forge->dropColumn('quizzes', ['time_limit', 'max_violations']);
    }
}