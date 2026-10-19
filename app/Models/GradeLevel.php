<?php

namespace App\Models;

use CodeIgniter\Model;

class GradeLevel extends Model
{
    protected $table = 'gradelevels';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'id',
        'name',
        'gradenumber',
        'created_at',
        'updated_at'
    ];

    public function gradeLevelStatistic()
    {

        $total = $this->db->table('gradelevels')->select('id')->countAll();
        $gradelevelwithsections = $this->db->table('sections s')
            ->select('COUNT(s.id) AS totalsections')
            ->join('gradelevels g', 'g.id = s.grade_level_id')
            ->get()->getRowObject();

        $totalenrolled = $this->db->table('enrollments e')
            ->select("
        COUNT(DISTINCT e.studentid) AS total_enrolled,
        COUNT(DISTINCT CASE
            WHEN p.gender = 'M' THEN e.studentid
        END) AS total_males,
        COUNT(DISTINCT CASE
            WHEN p.gender = 'F' THEN e.studentid
        END) AS total_females
    ")
            ->join('students s', 's.id = e.studentid')
            ->join('profiles p', 'p.id = s.profile_id')
            ->where('e.schoolyearid', get_settings()->schoolyear)
            ->get()
            ->getRowObject();
        return compact('total', 'gradelevelwithsections', 'totalenrolled');

    }

    public function getPerGradeLevelEnrollment(int $id)
    {
        return $this->db->table('enrollments e')
            ->select("
        COUNT(DISTINCT e.studentid) AS total_enrolled,
        COUNT(DISTINCT CASE
            WHEN p.gender = 'M' THEN e.studentid
        END) AS total_males,
        COUNT(DISTINCT CASE
            WHEN p.gender = 'F' THEN e.studentid
        END) AS total_females
    ")
            ->join('students s', 's.id = e.studentid')
            ->join('profiles p', 'p.id = s.profile_id')
            ->join('sections sec', 'sec.id = e.sectionid')
            ->join('gradelevels g', 'g.id = sec.grade_level_id')
            ->where('e.schoolyearid', get_settings()->schoolyear)
            ->where('sec.grade_level_id', $id)
            ->get()
            ->getRowObject();
    }

    public function sectionsPerGradeLevel($id)
    {
        return $this->db->table('sections s')
            ->select('COUNT(*) AS totalsections')
            ->join('gradelevels g', 'g.id = s.grade_level_id')
            ->where('g.id', $id)->get()->getResultObject();
    }

}
