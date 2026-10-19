<?php

namespace App\Libraries;

/**
 * Everything the quiz module needs to know about YOUR enrollment tables, in one place.
 * >>> Adjust the constants to your schema. <<<
 * Students are looked up by the id stored in session()->get('userdata')['id'].
 */
class QuizRoster
{
    // classes table (a class = a subject in a section)
    public const CLASS_TABLE = 'classes';
    public const COL = [
        'subject' => 'subjectid',
        'section' => 'sectionid',
        'schoolyear' => 'schoolyearid',   // optional: latest school year is used if the column does not exist
        'teacher' => 'teacherid',
    ];

    // which students are enrolled in a section
    public const ENROLL_TABLE = 'enrollments';
    public const ENROLL_STUDENT = 'studentid';
    public const ENROLL_SECTION = 'sectionid';

    // student records (id must equal the session user id) and how to display a name
    public const STUDENT_TABLE = 'students';
    public const STUDENT_NAME_SQL = "CONCAT(p.lastname, ', ', p.firstname)";

    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /** @return array<int, array{id: int|string, name: string}> */
    public function students(int $sectionId): array
    {
        return $this->db->table(self::ENROLL_TABLE . ' e')
            ->select('st.id AS id, ' . self::STUDENT_NAME_SQL . ' AS name', false)
            ->join(self::STUDENT_TABLE . ' st', 'st.id = e.' . self::ENROLL_STUDENT)
            ->join('profiles p', 'p.id = st.profile_id')
            ->where('e.' . self::ENROLL_SECTION, $sectionId)
            ->orderBy('name')->get()->getResultArray();
    }

    public function isEnrolled(int $studentId, int $sectionId): bool
    {
        return $studentId > 0 && $this->db->table(self::ENROLL_TABLE)
            ->where(self::ENROLL_STUDENT, $studentId)->where(self::ENROLL_SECTION, $sectionId)
            ->countAllResults() > 0;
    }

    /** @return int[] */
    public function sectionIds(int $studentId): array
    {
        $rows = $this->db->table(self::ENROLL_TABLE)->select(self::ENROLL_SECTION . ' AS sid')
            ->where(self::ENROLL_STUDENT, $studentId)->get()->getResultArray();

        return array_map('intval', array_column($rows, 'sid'));
    }
}
