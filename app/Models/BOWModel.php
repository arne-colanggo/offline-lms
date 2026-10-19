<?php

namespace App\Models;

use CodeIgniter\Model;

class BOWModel extends Model
{
    protected $table = 'bows';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = ['subjectid', 'term', 'name'];
    protected $useTimestamps = false; // handled by the DB defaults in the migration

    /** BOW list with subject + grade level + number of competencies */
    public function listWithSubject(): array
    {
        return $this->select(
            'bows.*, subjects.name AS subject_name, gradelevels.name AS grade_name,
             (SELECT COUNT(*) FROM bow_details d WHERE d.bowid = bows.id) AS competencies',
            false
        )
            ->join('subjects', 'subjects.id = bows.subjectid', 'left')
            ->join('gradelevels', 'gradelevels.id = subjects.grade_level_id', 'left')
            ->orderBy('gradelevels.id', 'ASC')
            ->orderBy('subjects.name', 'ASC')
            ->orderBy('bows.term', 'ASC')
            ->findAll();
    }

    public function findWithSubject(int $id)
    {
        return $this->select('bows.*, subjects.name AS subject_name, gradelevels.name AS grade_name')
            ->join('subjects', 'subjects.id = bows.subjectid', 'left')
            ->join('gradelevels', 'gradelevels.id = subjects.grade_level_id', 'left')
            ->where('subjects.id', $id)
            ->first();
    }

}
