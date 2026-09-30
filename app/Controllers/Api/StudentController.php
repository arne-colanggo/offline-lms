<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\AuthenticationServices;
use App\Controllers\Api\UserController;
use App\Models\ProfileModel;
use App\Models\StudentModel;
use App\Models\UsersModel;
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
                    'contact' => $this->request->getVar('contact'),
                    'email' => $this->request->getVar('email'),
                    'fbaccount' => $this->request->getVar('facebookurl')
                ])->where('id', $this->request->getVar('id'))->update();
                if ($profile_data) {
                    $student = new UsersModel;
                    $student->set([
                        'lrn' => $this->request->getVar('lrn'),
                        'fathersname' => $this->request->getVar('fathersname'),
                        'mothersname' => $this->request->getVar('mothersname'),
                        'guardiansname' => $this->request->getVar('guardian'),
                        'relationship' => $this->request->getVar('relationship'),
                        'guardian_parent_contact' => $this->request->getVar('parentcontact'),
                        'profile_id' => $this->request->getVar('id')

                    ])->update();
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
                    if ($row['addressid']) {
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


                if ($save) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Student Added']);

                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something is wrong']);

                }

            }


        }
    }
}
