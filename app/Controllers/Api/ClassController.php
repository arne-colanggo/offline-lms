<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Classes;
class ClassController extends BaseController
{

    protected $db;
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /** Dashboard: stats + create form + list of classes */
    public function index()
    {


        if (get_user()->userdata['role'] === 'admin') {

            $classes = $this->db->table('classes c')
                ->select('c.id, c.name, sy.name AS school_year, c.created_at, s.name AS section_name,
                      g.name AS grade_name, sub.name AS subject_name,
                      (SELECT COUNT(*) FROM class_students cs WHERE cs.classid = c.id) AS student_count')
                ->join('sections s', 's.id = c.sectionid')
                ->join('gradelevels g', 'g.id = s.grade_level_id', 'left')
                ->join('subjects sub', 'sub.id = c.subjectid')
                ->join('schoolyears sy', 'sy.id = c.schoolyearid')
                ->where('c.schoolyearid', get_settings()->schoolyear)
                ->orderBy('c.created_at', 'DESC')
                ->get()->getResultArray();

            $sections = $this->db->table('sections s')
                ->select('s.id, s.name, g.name AS grade_name')
                ->join('gradelevels g', 'g.id = s.grade_level_id', 'left')
                ->orderBy('g.name')->orderBy('s.name')
                ->get()->getResultArray();

            $stats = [
                'classes' => count($classes),
                'students_placed' => (int) $this->db->table('class_students cs')
                    ->join('classes c', 'c.id = cs.classid')
                    ->where('schoolyearid', get_settings()->schoolyear)->countAllResults(),
                'sections_covered' => (int) $this->db->table('classes')
                    ->select('sectionid')
                    ->distinct()
                    ->where('schoolyearid', get_settings()->schoolyear)
                    ->countAllResults(),
            ];


            return view('backend/pages/admin/classes', [
                'classes' => $classes,
                'sections' => $sections,
                'stats' => $stats,
                'pageTitle' => 'Classes'
            ]);
        } elseif (get_user()->userdata['role'] === 'teacher') {

            $classes = $this->db->table('classes c')
                ->select('c.id, c.name, sy.name AS school_year, c.created_at, s.name AS section_name,
                      g.name AS grade_name, sub.name AS subject_name,
                      (SELECT COUNT(*) FROM class_students cs WHERE cs.classid = c.id) AS student_count')
                ->join('sections s', 's.id = c.sectionid')
                ->join('gradelevels g', 'g.id = s.grade_level_id', 'left')
                ->join('subjects sub', 'sub.id = c.subjectid')
                ->join('schoolyears sy', 'sy.id = c.schoolyearid')
                ->where('c.schoolyearid', get_settings()->schoolyear)
                ->where('c.teacherid', get_user()->userdata['id'])
                ->orderBy('c.created_at', 'DESC')
                ->get()->getResultArray();

            $sections = $this->db->table('sections s')
                ->select('s.id, s.name, g.name AS grade_name')
                ->join('gradelevels g', 'g.id = s.grade_level_id', 'left')
                ->orderBy('g.name')->orderBy('s.name')
                ->get()->getResultArray();

            $stats = [
                'classes' => count($classes),
                'students_placed' => (int) $this->db->table('class_students cs')
                    ->join('classes c', 'c.id = cs.classid')
                    ->where('schoolyearid', get_settings()->schoolyear)
                    ->where('c.teacherid', get_user()->userdata['id'])
                    ->countAllResults(),
                'sections_covered' => (int) $this->db->table('classes')
                    ->select('sectionid')
                    ->distinct()
                    ->where('teacherid', get_user()->userdata['id'])
                    ->where('schoolyearid', get_settings()->schoolyear)
                    ->countAllResults(),
            ];


            return view('backend/pages/teacher/classes', [
                'classes' => $classes,
                'sections' => $sections,
                'stats' => $stats,
                'pageTitle' => 'Classes'
            ]);
        }

    }

    /**
     * AJAX: subjects offered by a section (that don't yet have a class
     * for the given school year) + how many enrollees the section has.
     */
    public function gradelevelSubjects()
    {
        $request = \Config\Services::request();
        $sectionId = $request->getVar('id');
        $schoolYear = get_settings()->schoolyear;

        $builder = $this->db->table('subjects sub')
            ->select('sub.id, sub.name')
            ->join('gradelevels gd', 'gd.id = sub.grade_level_id')
            ->join('sections se', 'se.grade_level_id = gd.id')
            ->where('se.id', $sectionId)
            ->orderBy('sub.name');


        $enrollees = $this->db->table('enrollments')
            ->where('sectionid', $sectionId)
            ->countAllResults();

        return $this->response->setJSON([
            'subjects' => $builder->get()->getResultArray(),
            'enrollees' => $enrollees,
        ]);
    }

    /** Create the class, then auto-add the section's enrollees */
    public function store()
    {
        $rules = [
            'section_id' => 'required|is_natural_no_zero',
            'subject_id' => 'required|is_natural_no_zero',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $sectionId = (int) $this->request->getPost('section_id');
        $subjectId = (int) $this->request->getPost('subject_id');
        $schoolYear = get_settings()->schoolyear;

        // 1) Subject must be offered in this section
        // $offered = $this->db->table('section_subjects')
        //     ->where(['section_id' => $sectionId, 'subject_id' => $subjectId])
        //     ->countAllResults();
        // if (!$offered) {
        //     return redirect()->back()->withInput()->with('error', 'That subject is not offered in the selected section.');
        // }

        // 2) No duplicate class
        $exists = $this->db->table('classes')
            ->where(['sectionid' => $sectionId, 'subjectid' => $subjectId, 'schoolyearid' => $schoolYear])
            ->countAllResults();
        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'A class for this subject and section already exists for ' . $schoolYear . '.');
        }

        $section = $this->db->table('sections')->where('id', $sectionId)->get()->getRowArray();
        $subject = $this->db->table('subjects')->where('id', $subjectId)->get()->getRowArray();
        $name = $subject['name'] . ' - ' . $section['name'];

        // 3) Create class + add enrollees atomically
        $this->db->transStart();
        $this->db->table('classes')->insert([
            'sectionid' => $sectionId,
            'subjectid' => $subjectId,
            'name' => $name,
            'schoolyearid' => $schoolYear
        ]);
        $classId = $this->db->insertID();
        $added = $this->addSectionEnrollees($classId, $sectionId);
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Could not create the class. Please try again.');
        }

        return redirect()->to(base_url('admin/classes'))
            ->with('success', "Class \"{$name}\" created with {$added} student(s) added.");
    }

    /** Re-sync: pull in students who enrolled in the section after the class was created */
    public function sync(int $classId)
    {
        $class = $this->db->table('classes')->where('id', $classId)->get()->getRowArray();
        if (!$class) {
            return redirect()->to(base_url('admin/classes'))->with('error', 'Class not found.');
        }

        $added = $this->addSectionEnrollees($classId, (int) $class['sectionid']);

        return redirect()->to(base_url('admin/classes'))->with('success', "{$added} new student(s) added to {$class['name']}.");
    }

    /** Copies every student enrolled in the section into the class. Returns how many were newly added. */
    private function addSectionEnrollees(int $classId, int $sectionId): int
    {
        $this->db->query(
            'INSERT IGNORE INTO class_students (classid, studentid, created_at)
             SELECT DISTINCT ?, e.studentid, NOW()
             FROM enrollments e
             WHERE e.sectionid = ?',
            [$classId, $sectionId]
        );

        return $this->db->affectedRows();
    }

    public function getClassDetails(int $id)
    {
        return $this->db->table('classes')
            ->select('
            classes.*,
            subjects.name AS subjectname,
            sections.name AS sectionname,
            gradelevels.name AS gradelevelname
        ')
            ->join('subjects', 'subjects.id = classes.subjectid', 'left')
            ->join('sections', 'sections.id = classes.sectionid', 'left')
            ->join('gradelevels', 'gradelevels.id = sections.grade_level_id', 'left')
            ->where('classes.id', $id)
            ->get()->getResult();   // returns an object if your model's $returnType is 'object'
    }

    public function getClassStats(int $id): array
    {
        $db = \Config\Database::connect();
        $classes = new Classes();
        $cd = $classes->asObject()->where('id', $id)->first();
        $enrolled = $db->table('class_students')->where('classid', $id)->countAllResults();
        //$quizzes = $db->table('quizzes')->where('classid', $id)->countAllResults();
        //$tasks = $db->table('performance_tasks')->where('classid', $id)->countAllResults();

        // Attendance rate: present records / all records for this class
        $total = $db->table('attendances')->select('studentid')->where('sectionid', $cd->sectionid)
            ->where('schoolyearid', get_settings()->schoolyear)
            ->distinct()
            ->countAllResults();
        $present = $db->table('attendances')->select('studentid')
            ->distinct()
            ->where('sectionid', $cd->sectionid)
            ->where('schoolyearid', get_settings()->schoolyear)
            ->where('attendancestatus', 'P')->countAllResults();

        return [
            'enrolled' => $enrolled,
            'quizzes' => 0,
            'tasks' => 0,
            'attendanceRate' => $total > 0 ? round(($present / $total) * 100, 1) : null,
        ];
    }
    public function viewClassDetails()
    {
        $id = (int) $this->request->getGet('id');
        $classdata = $this->getClassDetails($id);

        if (!$classdata) {
            return redirect()->to(route_to('classes'))->with('error', 'Class not found.');
        }

        $stats = $this->getClassStats($id);


        if (get_user()->userdata['role'] === 'admin') {
            return view('backend/pages/admin/classdetails', [
                'classdata' => $classdata[0],
                'enrolled' => $stats['enrolled'],
                'stats' => $stats,
            ]);
        } elseif (get_user()->userdata['role'] === 'teacher') {
            return view('backend/pages/teacher/classdetails', [
                'classdata' => $classdata[0],
                'enrolled' => $stats['enrolled'],
                'stats' => $stats,
            ]);
        }

    }
    public function postUpdateClass()
    {

        $id = $this->request->getPost('classid');
        $syid = get_settings()->schoolyear;
        $rules = [
            'classname' => 'required|is_unique[classes.id,{$id}]|is_unique[classes.schoolyearid,{$syid}]',
            'teacherid' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }
        $update = $this->db->table('classes')
            ->set(['name' => $this->request->getPost('classname'), 'teacherid' => $this->request->getPost('teacherid')])
            ->where('id', $id)
            ->update();
        if (!$update) {
            return $this->response->setJSON(['status' => 0, 'msg' => 'Unable to update', 'token' => csrf_hash()]);
        } else {
            return $this->response->setJSON(['status' => 1, 'msg' => 'Update successful']);
        }
    }
    public function exportEClass()
    {
        $id = $this->request->getGet('id');

        $rows = $this->db->table('classes c')
            ->select("CONCAT(p.lastname, ', ', p.firstname) AS fullname, p.gender, s.lrn", false)
            ->join('class_students cs', 'c.id = cs.classid')
            ->join('students s', 's.id = cs.studentid')
            ->join('profiles p', 'p.id = s.profile_id')
            ->where('c.id', $id)
            ->orderBy('p.lastname', 'ASC')
            ->orderBy('p.firstname', 'ASC')
            ->get()->getResultObject();

        $class = $this->db->table('classes c')
            ->select("s.name AS schoolyear, c.name as classname, sub.name as subject, CONCAT(p.lastname,', ',p.firstname) AS teacher")
            ->join('subjects sub', 'sub.id = c.subjectid')
            ->join('users u', 'u.id = c.teacherid', 'left')
            ->join('profiles p', 'p.id = u.profile_id', 'left')
            ->join('schoolyears s', 's.id = c.schoolyearid')
            ->where('c.id', $id)->get()->getResultObject();
        $cells = [
            'G4' => get_settings()->region,
            'O4' => get_settings()->division,
            'X4' => get_settings()->district,
            'G5' => get_settings()->schoolname,
            'X5' => get_settings()->schoolid,
            'AG5' => $class[0]->schoolyear,
            'K7' => $class[0]->classname,
            'S7' => $class[0]->teacher,
            'AG7' => $class[0]->subject,
        ];


        $males = [];
        $females = [];

        foreach ($rows as $r) {
            $gender = strtoupper(substr(trim($r->gender), 0, 1)); // works for 'M', 'Male', 'male'

            if ($gender === 'M') {
                $males[] = $r->fullname;
            } elseif ($gender === 'F') {
                $females[] = $r->fullname;
            }
        }
        for ($i = 0; $i < 60; $i++) {
            $cells['B' . (12 + $i)] = $males[$i] ?? '';
            $cells['B' . (73 + $i)] = $females[$i] ?? '';
        }

        $out = WRITEPATH . 'exports/' . uniqid('eclass_') . '.xlsx';
        (new \App\Libraries\EClassFiller())->fill(
            WRITEPATH . 'templates/2026-2027_Eclass.xlsx',
            $out,
            'xl/worksheets/sheet1.xml', // INPUT DATA
            $cells
        );

        return $this->response->download($out, null)->setFileName($class[0]->schoolyear . '_Eclass_' . $class[0]->classname . '.xlsx');
    }

    public function wheelOfNames()
    {

        $id = $this->request->getGet('id');

        $rows = $this->db->table('classes c')
            ->select("CONCAT(p.lastname, ', ', p.firstname) AS fullname", false)
            ->join('class_students cs', 'c.id = cs.classid')
            ->join('students s', 's.id = cs.studentid')
            ->join('profiles p', 'p.id = s.profile_id')
            ->where('c.id', $id)
            ->orderBy('p.lastname', 'ASC')
            ->orderBy('p.firstname', 'ASC')
            ->get()->getResultObject();

        $names = array_map(fn($r) => $r->fullname, $rows);
        $data = [
            'pageTitle' => 'Wheel of Names',
            'students' => $names
        ];

        return view('backend/pages/teacher/wheelofnames', $data);
    }
}
