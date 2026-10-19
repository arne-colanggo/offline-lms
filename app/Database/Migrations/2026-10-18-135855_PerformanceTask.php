<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PerformanceTasks extends Migration
{
    private function id()
    {
        return ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true];
    }

    private function fk($null = false)
    {
        return ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => $null];
    }

    private function ts()
    {
        return [
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ];
    }

    public function up()
    {
        // ---------- RUBRICS ----------
        $this->forge->addField(array_merge([
            'id' => $this->id(),
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'description' => ['type' => 'TEXT', 'null' => true],
            'class_id' => ['type' => 'INT'],
            'subject_id' => $this->fk(true),                       // optional: reusable per subject
            'created_by' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true], // users.id
        ], $this->ts()));
        $this->forge->addKey('id', true);
        $this->forge->addKey('subject_id');
        $this->forge->createTable('rubrics', true, ['ENGINE' => 'InnoDB']);

        // Rubric columns: performance levels (e.g. Excellent = 4 ... Beginning = 1)
        $this->forge->addField([
            'id' => $this->id(),
            'rubric_id' => $this->fk(),
            'label' => ['type' => 'VARCHAR', 'constraint' => 80],
            'points' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'ordering' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('rubric_id');
        $this->forge->addForeignKey('rubric_id', 'rubrics', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rubric_levels', true, ['ENGINE' => 'InnoDB']);

        // Rubric rows: criteria (e.g. Content, Creativity, Teamwork)
        $this->forge->addField([
            'id' => $this->id(),
            'rubric_id' => $this->fk(),
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'weight' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 1], // score = level points x weight
            'ordering' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('rubric_id');
        $this->forge->addForeignKey('rubric_id', 'rubrics', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rubric_criteria', true, ['ENGINE' => 'InnoDB']);

        // Rubric cells: descriptor text for each criterion x level
        $this->forge->addField([
            'id' => $this->id(),
            'criterion_id' => $this->fk(),
            'level_id' => $this->fk(),
            'description' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['criterion_id', 'level_id']);
        $this->forge->addForeignKey('criterion_id', 'rubric_criteria', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('level_id', 'rubric_levels', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rubric_descriptors', true, ['ENGINE' => 'InnoDB']);

        // ---------- PERFORMANCE TASKS ----------
        $this->forge->addField(array_merge([
            'id' => $this->id(),
            'class_id' => $this->fk(),                // the class (subject x section)
            'bow_details_id' => $this->fk(true),            // optional: BOW competency assessed
            'rubric_id' => $this->fk(),
            'title' => ['type' => 'VARCHAR', 'constraint' => 200],
            'instructions' => ['type' => 'TEXT', 'null' => true],
            'term' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 1],
            'due_date' => ['type' => 'DATE', 'null' => true],
            'is_published' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_by' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true],
        ], $this->ts()));
        $this->forge->addKey('id', true);
        $this->forge->addKey('class_id');
        $this->forge->addKey('bow_details_id');
        $this->forge->addForeignKey('rubric_id', 'rubrics', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('performance_tasks', true, ['ENGINE' => 'InnoDB']);

        // One row per student per task (overall result)
        $this->forge->addField(array_merge([
            'id' => $this->id(),
            'task_id' => $this->fk(),
            'lrn' => ['type' => 'VARCHAR', 'constraint' => 20],   // students.lrn
            'total_score' => ['type' => 'DECIMAL', 'constraint' => '7,2', 'default' => 0],
            'max_score' => ['type' => 'DECIMAL', 'constraint' => '7,2', 'default' => 0],
            'feedback' => ['type' => 'TEXT', 'null' => true],
            'scored_by' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'null' => true],
        ], $this->ts()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['task_id', 'lrn']);
        $this->forge->addForeignKey('task_id', 'performance_tasks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('performance_task_submissions', true, ['ENGINE' => 'InnoDB']);

        // Per-criterion rating inside a student's result
        $this->forge->addField([
            'id' => $this->id(),
            'submission_id' => $this->fk(),
            'criterion_id' => $this->fk(),
            'level_id' => $this->fk(),
            'score' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['submission_id', 'criterion_id']);
        $this->forge->addForeignKey('submission_id', 'performance_task_submissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('criterion_id', 'rubric_criteria', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('level_id', 'rubric_levels', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('performance_task_scores', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('performance_task_scores', true);
        $this->forge->dropTable('performance_task_submissions', true);
        $this->forge->dropTable('performance_tasks', true);
        $this->forge->dropTable('rubric_descriptors', true);
        $this->forge->dropTable('rubric_criteria', true);
        $this->forge->dropTable('rubric_levels', true);
        $this->forge->dropTable('rubrics', true);
    }
}