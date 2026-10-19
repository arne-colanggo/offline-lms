<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'lrn',
        'fathersname',
        'mothersname',
        'guardiansname',
        'relationship',
        'parent_guardian_contact',
        'remarks',
        'profile_id'
    ];


    public function currentEnrolledClasses(int $id)
    {

        // $sql = "SELECT c.id, c.classname, s.`name` AS subjectname, sc.`name` AS sectionname, g.`name` AS gradelevel FROM ((((classes c INNER JOIN subjects s ON s.id = c.subjectid )INNER JOIN sections sc ON sc.id = c.sectionid) INNER JOIN gradelevels g ON g.id = sc.grade_level_id) INNER JOIN classes cl ON cl.subjectid = s.id) INNER JOIN classes_enrolled ce ON cl.id = ce.classid WHERE ce.studentid = " . $this->Auth::id() . " AND ce.schoolyearid = " . get_settings()->schoolyear;

        return $this->db->table('students s')
            ->select('c.id, c.name AS classname, sub.name AS subjectname, sec.name AS sectionname, gl.name AS gradelevel')
            ->join('class_student cs ', 'cs.studentid = s.id')
            ->join('classes c', 'c.id = cs.classid')
            ->join('subjects sub', 'sub.id = c.subjectid')
            ->join('sections sec', 'sec.id = c.sectionid')
            ->join('gradelevels gl', 'gl.id = sec.grade_level_id')
            ->where('s.id', $id)
            ->where('c.schoolyearid', get_settings()->schoolyear)
            ->get()->getResultObject();
    }

}
