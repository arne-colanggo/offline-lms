<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\AuthenticationServices;
use App\Controllers\Api\UserController;
use App\Models\ProfileModel;
use App\Models\StudentModel;
use App\Models\UsersModel;
use App\Models\Enrollment;
use App\Models\Sections;
use Carbon\Carbon;

use SSP;
class StudentController extends BaseController
{
    protected $db;
    protected AuthenticationServices $Auth;
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    public function __construct()
    {
        require_once APPPATH . 'ThirdParty/ssp.php';
        $this->db = db_connect();
    }

    public function student()
    {
        $request = \Config\Services::request();
        $id = $request->getVar('id');
        if (isset($id)) {
            $profile = new ProfileModel();
            $profile_data = $profile->asObject()->where('id', $id)->first();
            $student = new StudentModel();
            $profile_data->address = get_address($profile_data->addressid);
            $profile_data->student = $student->asObject()->where('profile_id', $id)->first();

        }

        $data = [
            'pageTitle' => 'Student Profile',
            'profile' => isset($profile_data) ? $profile_data : null,
        ];
        return view('backend/pages/users/student-profile', $data);
    }
    public function postStudent()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();

            $this->validate([
                'id' => 'permit_empty|integer',
                'lrn' => [
                    'rules' => 'required|max_length[12]|min_length[12]',
                    'errors' => [
                        'required' => 'LRN is required',
                        'min_length' => 'Minimum leght is 12',
                        'max_length' => 'Maximum lenght is 12'
                    ]
                ],
                'firstname' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'First name is required'
                    ]
                ],
                'middlename' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Middle name is required if no middle name use (-)'
                    ]
                ],
                'lastname' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Lastname is required'
                    ]
                ],
                'gender' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Gender is required'
                    ]
                ],
                'dateofbirth' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Date of Birth is required'
                    ]
                ],
                'barangay_id' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Address is required'
                    ]
                ],
                'fathersname' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Fathers name is required if none just type (-)'
                    ]
                ],
                'mothersname' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Mothers name is required if none just type (-)'
                    ]
                ],
            ]);

        }

        if ($this->validator->run() === false) {
            return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'errors' => $this->validator->getErrors()]);
        } else {
            $profile = new ProfileModel();

            if ($request->getVar('id') > 0) {
                $profile_data = $profile->set([
                    'firstname' => $this->request->getVar('firstname'),
                    'middlename' => $this->request->getVar('middlename'),
                    'lastname' => $this->request->getVar('lastname'),
                    'gender' => $this->request->getVar('gender'),
                    'dateofbirth' => $this->request->getVar('dateofbirth'),
                    'street' => $this->request->getVar('street'),
                    'addressid' => $this->request->getVar('barangay_id'),
                    'religion' => $this->request->getVar('religion'),
                    'contact' => $this->request->getVar('studentcontact'),
                    'email' => $this->request->getVar('email'),
                    'fbaccount' => $this->request->getVar('facebookurl')
                ])->where('id', $this->request->getVar('id'))->update();
                if ($profile_data) {
                    $student = new StudentModel();
                    $student->set([
                        'lrn' => $this->request->getVar('lrn'),
                        'fathersname' => $this->request->getVar('fathersname'),
                        'mothersname' => $this->request->getVar('mothersname'),
                        'guardiansname' => $this->request->getVar('guardian'),
                        'relationship' => $this->request->getVar('relationship'),
                        'guardian_parent_contact' => $this->request->getVar('parentcontact')

                    ])->where('profile_id', $this->request->getVar('id'))->update();
                }
                if ($profile_data) {
                    return $this->response->setJSON(['status' => 1, 'msg' => 'Update Success']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'msg' => 'Unable to update']);
                }
            } else {
                $profile_data = $profile->save([
                    'firstname' => $this->request->getVar('firstname'),
                    'middlename' => $this->request->getVar('middlename'),
                    'lastname' => $this->request->getVar('lastname'),
                    'gender' => $this->request->getVar('gender'),
                    'dateofbirth' => $this->request->getVar('dateofbirth'),
                    'street' => $this->request->getVar('street'),
                    'addressid' => $this->request->getVar('barangay_id'),
                    'religion' => $this->request->getVar('religion'),
                    'contact' => $this->request->getVar('contact'),
                    'email' => $this->request->getVar('email'),
                    'fbaccount' => $this->request->getVar('facebookurl')
                ]);
                $profile_id = $profile->getInsertID();
                if ($profile_id) {
                    $student = new StudentModel();
                    $student->save([
                        'lrn' => $this->request->getVar('lrn'),
                        'fathersname' => $this->request->getVar('fathersname'),
                        'mothersname' => $this->request->getVar('mothersname'),
                        'guardiansname' => $this->request->getVar('guardian'),
                        'relationship' => $this->request->getVar('relationship'),
                        'guardian_parent_contact' => $this->request->getVar('parentcontact'),
                        'profile_id' => $profile_id

                    ]);
                    $users = new UsersModel();
                    $users->save([
                        'username' => $this->request->getVar('lrn'),
                        'password' => password_hash($this->request->getVar('lrn'), PASSWORD_BCRYPT),
                        'role' => 'student',
                        'status' => 1,
                        'profile_id' => $profile_id
                    ]);

                    if ($profile_data) {
                        return $this->response->setJSON(['status' => 1, 'msg' => 'New Record Success']);
                    } else {
                        return $this->response->setJSON(['status' => 0, 'msg' => 'Unable to update']);
                    }
                }
            }
        }
    }
    public function viewStudent()
    {
        $data = [
            'pageTitle' => 'Student'
        ];
        return view('backend/pages/admin/studentlist', $data);
    }
    public function studentsList()
    {
        $dbDetails = array(
            "host" => $this->db->hostname,
            "user" => $this->db->username,
            "pass" => $this->db->password,
            "db" => $this->db->database,
        );

        $table = "students";
        $joinQuery = "FROM `students` AS `s` LEFT JOIN `profiles` AS `p` ON (`s`.`profile_id` = `p`.`id`)";

        $primaryKey = "id";

        $columns = array(
            array(
                "db" => "s.id",
                "dt" => 0,
                "as" => "studentid",
                "field" => "studentid",
            ),
            array(
                "db" => "lrn",
                "dt" => 1,
                "field" => "lrn",
            ),
            array(
                "db" => "CONCAT(p.firstname, ' ',p.middlename, ' ', p.lastname)",
                "dt" => 2,
                "as" => "fullname",
                "field" => "fullname"

            ),
            array(
                "db" => "p.contact",
                "dt" => 3,
                "field" => "contact"
            ),
            array(
                "db" => "p.addressid",
                "dt" => 4,
                "field" => "addressid",
                "formatter" => function ($d, $row) {
                    if ($row['addressid'] > 0 | !$row['addressid'] == null) {
                        $addres = get_address($row['addressid']);
                        return $addres->province_name . ' ' . $addres->municipality_name . ' ' . $addres->barangay_name;

                    } else {
                        return null;
                    }

                },
            ),
            array(
                "db" => "s.profile_id",
                "dt" => 5,
                "field" => "profile_id",
                "formatter" => function ($d, $row) {
                    return '<div class="btn-group">
                        <a href="' . route_to('student.profile') . '/?id=' . $row['profile_id'] . '" class="btn btn-sm btn-link mx-1 editStudentBtn" data-id="' . $row['id'] . '">Edit</a>
                        <button class="btn btn-sm btn-link mx-1 enrolStudentBtn" data-id="' . $row['id'] . '">Enrol</button>
                        <button class="btn btn-sm btn-link mx-1 deleteStudentBtn" data-id="' . $row['id'] . '">Delete</button>
                    </div>';
                },
            ),
            array(
                "db" => "s.id",
                "dt" => 6,
                "field" => "id",
                "formatter" => function ($d, $row) {
                    return '<div class="btn-group">
                        <button class="btn btn-sm btn-link mx-1 section_enrolStudentBtn" data-id="' . $row['id'] . '">Enrol</button>
                    </div>';
                },
            ),
            array(
                "db" => "s.id",
                "dt" => 7,
                "field" => "id",
                "formatter" => function ($d, $row) {
                    return '<div class="btn-group">
                        <button class="btn btn-sm btn-link mx-1 enrol-to-class-btn" data-id="' . $row['id'] . '">Enrol</button>
                    </div>';
                },
            ),
        );

        return json_encode(
            SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns, $joinQuery)
        );
    }
    public function uploadSF1()
    {
        $data = [
            'pageTitle' => 'Upload SF1'
        ];
        return view('backend/pages/student/uploadsfone', $data);
    }


    public function postUploadSF1()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            if (!empty($request->getVar('data'))) {
                $this->validate([
                    'data' => [
                        'rules' => 'required',
                        'errors' => [
                            'required' => 'Record must not empty',
                        ]
                    ]
                ]);

                if ($validation->run() === FALSE) {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $validation->getErrors()]);
                } else {
                    $students = new StudentModel();
                    $profile = new ProfileModel();
                    $users = new UsersModel();
                    $data = array();
                    $data = json_decode($request->getVar('data'));


                    foreach ($data as $key => $value) {
                        $name = splitFullName($value[1]);
                        $LastName = $name['last_name'];
                        $FirstName = $name['first_name'];
                        $MiddleName = $name['middle_name'];

                        $date = date_create($value[3]);

                        if ($date !== false) {
                            $myDateTime = $date->format('Y-m-d');
                        } else {
                            $myDateTime = null; // or handle the error
                        }
                        //check first if lrn is already exist

                        $exist_lrn = $students->asObject()->where('lrn', $value[0])->first();

                        if ($exist_lrn) {
                            //update already exist

                        } else {
                            $save = $profile->save([
                                'firstname' => $FirstName,
                                'middlename' => $MiddleName,
                                'lastname' => $LastName,
                                'gender' => $value[2],
                                'dateofbirth' => $myDateTime,
                                'street' => $value[8],
                                'religion' => $value[7],
                            ]);
                            $profile_id = $profile->getInsertID();
                            if ($profile_id) {

                                $students->save([
                                    'lrn' => $value[0],
                                    'fathersname' => $value[12],
                                    'mothersname' => $value[13],
                                    'guardiansname' => $value[14],
                                    'relationship' => $value[15],
                                    'guardian_parent_contact' => $value[16],
                                    'profile_id' => $profile_id

                                ]);

                                $users->save([
                                    'username' => $value[0],
                                    'email' => $value[0] . "@deped.gov.ph",
                                    'password' => password_hash($value[0], PASSWORD_BCRYPT),
                                    'role' => 'student',
                                    'status' => 1,
                                    'profile_id' => $profile_id
                                ]);


                            }
                        }

                    }

                    if (isset($save)) {
                        return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Student Added']);
                    } else {
                        return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something is wrong']);

                    }

                }

            } else {
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'No Record to save!']);
            }
        }
    }
    //enrollment

    public function postSingleEnrollment()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            $this->validate([
                'studentid' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Student is required'
                    ]
                ],

                'section' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Section is required'
                    ]
                ],

                'schoolyear' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'School year is required'
                    ]
                ],
                'dateenrolled' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Date enrolled is required'
                    ]
                ],
            ]);
        }

        if ($validation->run() === FALSE) {
            $errors = $validation->getErrors();
            return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $errors]);
        } else {

            $enrolment = new Enrollment();
            $save = $enrolment->asObject()->save([
                'studentid' => $request->getVar('studentid'),
                'sectionid' => $request->getVar('section'),
                'schoolyearid' => $request->getVar('schoolyear'),
                'dateenrolled' => $request->getVar('dateenrolled'),
            ]);

            if ($save) {
                return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Student Enrolled Successfully']);
            } else {
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong!']);
            }
        }
    }

    function getEnrolledStudents()
    {
        $request = \Config\Services::request();
        $sectionid = $request->getVar('id');
        $schoolyearid = $request->getVar('sid');

        $dbDetails = array(
            "host" => $this->db->hostname,
            "user" => $this->db->username,
            "pass" => $this->db->password,
            "db" => $this->db->database,
        );

        $table = "enrollments";
        $primaryKey = "id";
        $joinQuery = "FROM `enrollments` AS `e` INNER JOIN `students` AS `s` ON (`s`.`id` = `e`.`studentid`) JOIN profiles p ON p.id = s.profile_id";
        $extraWhere = "`e`.`sectionid`=" . $sectionid . " AND `e`.`schoolyearid`=" . $schoolyearid;

        $columns = array(
            array(
                "db" => "e.id",
                "dt" => 0,
                "field" => "id",
            ),

            array(
                "db" => "s.lrn",
                "dt" => 1,
                "field" => "lrn",
            ),
            array(
                "db" => "CONCAT(p.lastname, ', ', p.firstname, ' ', p.middlename)",
                "dt" => 2,
                "field" => "fullname",
                "as" => 'fullname'
            ),
            array(
                "db" => "p.contact",
                "dt" => 3,
                "field" => "contact",
            ),
            array(
                "db" => "p.addressid",
                "dt" => 4,
                "field" => "addressid",
                "as" => 'addressid',
                "formatter" => function ($d, $row) {
                    if ($row['addressid'] > 0 | !$row['addressid'] == null) {
                        $addres = get_address($row['addressid']);
                        return $addres->province_name . ' ' . $addres->municipality_name . ' ' . $addres->barangay_name;

                    } else {
                        return null;
                    }

                },
            ),
            array(
                "db" => "s.id",
                "dt" => 5,
                "field" => "id",
                "as" => "studentid",
                "formatter" => function ($d, $row) {
                    return '<div class="btn-group">
                    <a href="' . route_to('add-student') . '/?id=' . $row["studentid"] . '" class="btn btn-sm btn-link mx-1" data-id="' . $row["studentid"] . '"><i class="icon-copy dw dw-user-12"></i></a>
                    <a href="' . route_to('edit-lesson-plan') . '/?id=' . $row["studentid"] . '" class="btn btn-sm btn-link mx-1" data-id="' . $row['id'] . '"><i class="icon-copy dw dw-profits"></i></a>
                </div>';
                },
            ),
            array(
                "db" => "e.id",
                "dt" => 6,
                "field" => "id",
                "formatter" => function ($d, $row) {
                    return '<div class="btn-group">
                    <button id="unenrol_btn" class="btn btn-sm btn-link mx-1" data-id="' . $row['id'] . '"><i class="icon-copy dw dw-remove"></i></button>
                    
                </div>';
                },
            )
        );

        return json_encode(
            SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
        );

    }

    function batchEnrollStudentSection()
    {
        $request = \Config\Services::request();
        $sectionid = $request->getVar('id');
        $section = new Sections();
        $d = $this->db->table('sections s')
            ->select('gl.name as grade_level_name, s.name as section_name')
            ->join('gradelevels gl', 'gl.id = s.grade_level_id')
            ->where('s.id', $sectionid)->get()->getResult();
        $data = [
            'pageTitle' => 'Batch Enroll Student',
            'sectionid' => $sectionid,
            'sectionname' => $d[0]->grade_level_name . ' ' . $d[0]->section_name

        ];
        return view('backend/pages/admin/batchenrollstudentsection', $data);
    }

    public function postBatchEnrollStudentSection()
    {
        $request = \Config\Services::request();
        // if ($request->isAJAX()) {
        //     $validation = \Config\Services::validation();
        //     $this->validate([
        //         'data' => [
        //             'rules' => 'required',
        //             'errors' => [
        //                 'required' => 'Data is empty'
        //             ]
        //         ]
        //     ]);

        //     if ($validation->run() === FALSE) {
        //         return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'errors' => $this->$validation->getErrors()]);
        //     } else {
        //         $this->response->setJSON(['status' => 1, 'msg' => $this->request->getVar('data')]);
        //     }
        // }
    }

    public function postBatchEnrollStudentSections()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            if (!empty(json_decode($request->getVar('data')))) {
                $this->validate([
                    'data' => [
                        'rules' => 'required',
                        'errors' => [
                            'required' => 'Record must not empty',
                        ]
                    ]
                ]);

                if ($validation->run() === FALSE) {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $validation->getErrors()]);
                } else {

                    try {
                        //code...
                        $students = new StudentModel();
                        $profile = new ProfileModel();
                        $users = new UsersModel();
                        $enrollment = new Enrollment();
                        $data = array();
                        $data = json_decode($request->getVar('data'));

                        foreach ($data as $key => $value) {
                            //check if the existing student is already existed by checking the lrn

                            $sd = $students->asObject()->where('lrn', $value[0])->first();
                            if ($sd) {
                                //check if currently enrolled by this schoolyear and section
                                $exist_enrolled = $enrollment->asObject()->where('studentid', $sd->id)->where('schoolyearid', get_settings()->schoolyear)->first();

                                if (!$exist_enrolled) {
                                    // if not currently enrolled do the enrollment
                                    // but before doing the enrollment lets check the profile
                                    $enrollment->save([
                                        'studentid' => $sd->id,
                                        'sectionid' => $this->request->getVar('sectionid'),
                                        'schoolyearid' => get_settings()->schoolyear,
                                        'dateenrolled' => Carbon::now()
                                    ]);
                                }
                            } else {
                                //students not existed register students

                                $name = splitFullName($value[1]);
                                $LastName = $name['last_name'];
                                $FirstName = $name['first_name'];
                                $MiddleName = $name['middle_name'];

                                $date = date_create($value[3]);

                                if ($date !== false) {
                                    $myDateTime = $date->format('Y-m-d');
                                } else {
                                    $myDateTime = null; // or handle the error
                                }
                                $profile->save([
                                    'firstname' => $FirstName,
                                    'middlename' => $MiddleName,
                                    'lastname' => $LastName,
                                    'gender' => $value[2],
                                    'dateofbirth' => $myDateTime,
                                    'street' => $value[8],
                                    'religion' => $value[7],
                                ]);
                                $profile_id = $profile->getInsertID();
                                if ($profile_id) {

                                    $students->save([
                                        'lrn' => $value[0],
                                        'fathersname' => $value[12],
                                        'mothersname' => $value[13],
                                        'guardiansname' => $value[14],
                                        'relationship' => $value[15],
                                        'guardian_parent_contact' => $value[16],
                                        'profile_id' => $profile_id

                                    ]);

                                    $users->save([
                                        'username' => $value[0],
                                        'email' => $value[0] . "@deped.gov.ph",
                                        'password' => password_hash($value[0], PASSWORD_BCRYPT),
                                        'role' => 'student',
                                        'status' => 1,
                                        'profile_id' => $profile_id
                                    ]);
                                    $enrollment->save([
                                        'studentid' => $students->getInsertID(),
                                        'sectionid' => $this->request->getVar('sectionid'),
                                        'schoolyearid' => get_settings()->schoolyear,
                                        'dateenrolled' => Carbon::now()
                                    ]);
                                }
                            }

                        }
                        return $this->response->setJSON(['status' => 1, 'msg' => 'Record added successfully']);
                    } catch (\Throwable $th) {
                        //throw $th;
                        return $this->response->setJSON(['status' => 0, 'msg' => 'Something went wrong']);
                    }

                }

            } else {
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'No Record to save!']);
            }
        }
    }
    public function uploadAttendanceBySection()
    {
        $request = \Config\Services::request();
        $data = [
            'pageTitle' => 'Upload Attendances by Section',
            'sectionid' => $request->getVar('sectionid')
        ];

        return view('backend/pages/admin/uploadattendancesbysection', $data);
    }
}
