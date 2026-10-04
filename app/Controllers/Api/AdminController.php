<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SchoolYear;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SettingsModel;
use App\Models\GradeLevel;
use App\Models\Sections;
use SSP;
class AdminController extends BaseController
{
    protected $db;
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    public function __construct()
    {
        require_once APPPATH . 'ThirdParty/ssp.php';
        $this->db = db_connect();
    }

    public function updateGeneralSettings()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();

            $this->validate([
                'schoolname' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'School name is required'
                    ]
                ],
                'schooladdress' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'School address is required'
                    ]
                ],
                'phone' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Phone number is required'
                    ]
                ],
                'email' => [
                    'rules' => 'required|valid_email',
                    'errors' => [
                        'required' => 'Email is required',
                        'valid_email' => 'Invalid email'
                    ]
                ],
                'schoolhead' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'School head is required'
                    ]
                ],
                'schoolheaddesignation' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Designation is required'
                    ]
                ],
                // 'schoolyear' => [
                //     'rules' => 'required',
                //     'errors' => [
                //         'required' => 'School year is required'
                //     ]
                // ]

            ]);
            if ($validation->run() === false) {
                return $this->response->setJSON(['status' => 0, 'errors' => $validation->getErrors(), 'token' => csrf_hash()]);
            } else {
                $settings = new SettingsModel();
                $data = [
                    'schoolname' => $request->getVar('schoolname'),
                    'schooladdress' => $request->getVar('schooladdress'),
                    'phone' => $request->getVar('phone'),
                    'email' => $request->getVar('email'),
                    'schoolyear' => $request->getVar('schoolyear'),
                    'schoolhead' => $request->getVar('schoolhead'),
                    'schoolheaddesignation' => $request->getVar('schoolheaddesignation')
                ];
                $update = $settings->set($data)->where('id', 1)->update();
                if ($update) {
                    return $this->response->setJSON(['status' => 1, 'msg' => 'Settings Updated Successfully']);
                }

                return $this->response->setJSON(['status' => 0, 'msg' => 'Unable to update settings']);
            }
        }
    }

    public function updateLogo()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $settings = new SettingsModel();
            $path = 'images/settings/';
            $file = $request->getFile('logo');
            $settings_data = $settings->asObject()->first();
            $old_logo = $settings_data->logo;
            $new_filename = 'logo' . $file->getRandomName();

            if ($file->move($path, $new_filename)) {
                if ($old_logo != null && file_exists($path . $old_logo)) {
                    unlink($path . $old_logo);
                }

                $update = $settings->where('id', $settings_data->id)
                    ->set(['logo' => $new_filename])
                    ->update();

                if ($update) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Logo updated successfully']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Unable to upload logo']);
                }
            } else {
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong!']);
            }
        }
    }

    function updateFavicon()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $settings = new SettingsModel();
            $path = 'images/settings/';
            $settings_data = $settings->asObject()->first();
            $file = $request->getFile('favicon');
            $old_favicon = $settings_data->favicon;
            $new_favicon = 'Favicon' . $file->getRandomName();

            if ($file->move($path, $new_favicon)) {
                if ($old_favicon != null && file_exists($path . $old_favicon)) {
                    unlink($path . $old_favicon);
                }

                $update = $settings->where('id', $settings_data->id)
                    ->set(['favicon' => $new_favicon])
                    ->update();
                if ($update) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Done! Favicon uploaded successfully']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Unable to Upload Favicon']);
                }
            } else {
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Error while uploading favicon!']);
            }


        }
    }
    public function postSchoolYear()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            $this->validate([
                'school_year_name' => [
                    'rules' => 'is_unique[schoolyears.name]',
                    'errors' => [
                        'is_unique' => 'Already Exist!'
                    ]
                ]
            ]);
            if ($validation->run() === FALSE) {
                return $this->response->setJSON(['status' => 0, 'errors' => $validation->getErrors()]);
            } else {
                $schoolyear = new SchoolYear();
                $sy = $schoolyear->save([
                    'name' => $this->request->getVar('school_year_name'),
                ]);

                if ($sy) {
                    return $this->response->setJSON(['status' => 1, 'msg' => 'New School Year Added']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'msg' => 'Unable to Add new School Year']);
                }

            }
        }
    }
    public function getcboSchoolYear()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $id = $request->getVar('schoolyearid');
            $schoolyear = new SchoolYear();
            $options = '';
            $parent_schoolyear = $schoolyear->findAll();

            if (count($parent_schoolyear)) {
                $added_options = '';
                foreach ($parent_schoolyear as $schoolyear) {
                    $isSelected = $schoolyear['id'] == $id ? 'selected' : '';
                    $added_options .= '<option value="' . $schoolyear['id'] . '" ' . $isSelected . ' >' . $schoolyear['name'] . '</option>';
                }

                $options = $options . $added_options;

                return $this->response->setJSON(['status' => 1, 'data' => $options]);

            } else {
                return $this->response->setJSON(['status' => 0, 'data' => $options]);
            }

        }
    }
    public function getSchoolYear()
    {
        $request = \Config\Services::request();
        $id = $request->getVar('school_year_id');
        $schoolyear = new SchoolYear();

        if ($request->isAJAX()) {
            if (isset($id) | $id > 0) {
                $sy = $schoolyear->asObject()->where('id', $id)->first();
                return $this->response->setJSON(['status' => 1, 'data' => $sy]);
            } else {
                $sy = $schoolyear->findAll();
                return $this->response->setJSON(['status' => 1, 'data' => $sy]);

            }
        }
    }
    public function updateSchoolYear()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            $this->validate([
                'school_year_name' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'School Year is required'
                    ]
                ]
            ]);

            if ($validation->run() === FALSE) {
                return $this->response->setJSON(['status' => 0, 'errors' => $validation->getErrors()]);
            } else {
                $sy = new SchoolYear();
                $update = $sy->set([
                    'name' => $this->request->getVar('school_year_name')
                ])->where('id', $this->request->getVar('school_year_id'))->update();
                if ($update) {
                    return $this->response->setJSON(['status' => 1, 'msg' => 'School Year Updated!']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'msg' => 'Unable to update']);
                }
            }
        }
    }

    function getSchoolYears()
    {

        $dbDetails = array(
            "host" => $this->db->hostname,
            "user" => $this->db->username,
            "pass" => $this->db->password,
            "db" => $this->db->database,
        );

        $table = "schoolyears";
        $primaryKey = "id";

        $columns = array(
            array(
                "db" => "id",
                "dt" => 0
            ),
            array(
                "db" => "name",
                "dt" => 1
            ),
            array(
                "db" => "id",
                "dt" => 2,
                "formatter" => function ($d, $row) {
                    return '<div class="btn-group">
                    <button class="btn btn-sm btn-link mx-1 editSchoolYearBtn" data-id="' . $row['id'] . '">Edit</button>
                    <button class="btn btn-sm btn-link mx-1 deleteSchoolYearBtn" data-id="' . $row['id'] . '">Delete</button>
                </div>';
                },
            )
        );

        return json_encode(
            SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns)
        );
    }

    public function deleteSchoolYear()
    {
        $request = \Config\Services::request();
        $id = $request->getVar('school_year_id');
        if ($id > 0) {
            $sy = new SchoolYear();
            $del = $sy->where('id', $id)->delete();
            if ($del) {
                return $this->response->setJSON(['status' => 1, 'msg' => 'Deleted']);
            } else {
                return $this->response->setJSON(['status' => 0, 'msg' => 'Unable to delete']);
            }
        }
    }
    public function schoolyear()
    {
        $data = [
            'pageTitle' => 'School Year'
        ];
        return view('backend/pages/admin/schoolyear', $data);
    }

    // Grade Level

    public function gradelevel()
    {
        $data = [
            'pageTitle' => 'Grade Level'
        ];
        return view('backend/pages/admin/gradelevel', $data);
    }
    public function getGradeLevels()
    {
        $dbDetails = array(
            "host" => $this->db->hostname,
            "user" => $this->db->username,
            "pass" => $this->db->password,
            "db" => $this->db->database,
        );

        $table = "gradelevels";
        $primaryKey = "id";

        $columns = array(
            array(
                "db" => "id",
                "dt" => 0
            ),
            array(
                "db" => "name",
                "dt" => 1
            ),
            array(
                "db" => "id",
                "dt" => 2,
                "formatter" => function ($d, $row) {
                    return '<div class="btn-group">
                        <button class="btn btn-sm btn-link mx-1 editGradeLevelBtn" data-id="' . $row['id'] . '">Edit</button>
                        <button class="btn btn-sm btn-link mx-1 deleteGradeLevelBtn" data-id="' . $row['id'] . '">Delete</button>
                    </div>';
                },
            )
        );

        return json_encode(
            SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns)
        );
    }

    public function postGradeLevel()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();

            $this->validate(
                [
                    'grade_level_name' => [
                        'rules' => 'required|is_unique[gradelevels.name]',
                        'errors' => [
                            'required' => 'Grade level is required!',
                            'is_unique' => 'This grade level already exists.'
                        ]
                    ]
                ]
            );

            if ($validation->run() === false) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $errors]);
            } else {
                $data = [
                    'name' => $request->getVar('grade_level_name')
                ];
                $gradelevel = new GradeLevel();
                $result = $gradelevel->insert($data);

                if ($result) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'New Grade Level Added!']);
                } else {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Something went Wrong!']);
                }
            }
        }
    }

    function getGradeLevel()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $id = $request->getVar('grade_level_id');
            $gradelevel = new GradeLevel();
            $result = $gradelevel->find($id);
            if ($result) {
                return $this->response->setJSON(['data' => $result]);
            }
        }
    }
    function updateGradeLevel()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            $id = $request->getVar('grade_level_id');
            $this->validate([
                'grade_level_name' => [
                    'rules' => 'required|is_unique[gradelevels.name,id,' . $id . ']',
                    'errors' => [
                        'required' => 'Grade Level name must not empty',
                        'is_unique' => 'This name already exist',
                    ]
                ]
            ]);

            if ($validation->run() === FALSE) {
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $validation->getErrors()]);
            } else {
                $gradelevel = new GradeLevel();
                $update = $gradelevel->where('id', $id)
                    ->set(['name' => $request->getVar('grade_level_name')])
                    ->update();
                if ($update) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Grade level updated successfully!']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong while updating!']);
                }
            }

        }
    }

    function deleteGradeLevel()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $id = $request->getVar('grade_level_id');
            $gradelevel = new GradeLevel();
            $delete = $gradelevel->where('id', $id)->delete();

            if ($delete) {
                return $this->response->setJSON(['status' => 1, 'msg' => 'Grade level deleted successfully!']);
            } else {
                return $this->response->setJSON(['status' => 0, 'msg' => 'Action Failed!']);
            }
        }
    }

    function getParentGradeLevel()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $id = $request->getVar('parent_grade_level_id');
            $gradelevel = new GradeLevel();
            $options = '';
            $parent_grade_level = $gradelevel->findAll();

            if (count($parent_grade_level)) {
                $added_options = '';
                foreach ($parent_grade_level as $parent_grade) {
                    $isSelected = $parent_grade['id'] == $id ? 'selected' : '';
                    $added_options .= '<option value="' . $parent_grade['id'] . '" ' . $isSelected . ' >' . $parent_grade['name'] . '</option>';
                }

                $options = $options . $added_options;

                return $this->response->setJSON(['status' => 1, 'data' => $options]);

            } else {
                return $this->response->setJSON(['status' => 0, 'data' => $options]);
            }

        }
    }

    public function section()
    {
        $data = [
            'pageTitle' => 'Section'
        ];
        return view('backend/pages/admin/section', $data);
    }
    public function getSections()
    {
        $gradelevel = new GradeLevel();
        $section = new Sections();
        $dbDetails = array(
            "host" => $this->db->hostname,
            "user" => $this->db->username,
            "pass" => $this->db->password,
            "db" => $this->db->database,
        );

        $table = "sections";
        $primaryKey = "id";

        $columns = array(
            array(
                "db" => "id",
                "dt" => 0
            ),
            array(
                "db" => "name",
                "dt" => 1
            ),
            array(
                "db" => "id",
                "dt" => 2,
                "formatter" => function ($d, $row) use ($gradelevel, $section) {
                    $parent_grade_id = $section->asObject()->where("id", $row['id'])->first()->grade_level_id;
                    $grade_level_name = ' - ';

                    if ($parent_grade_id != 0) {
                        $grade_level_name = $gradelevel->asObject()->where('id', $parent_grade_id)->first()->name;
                    }
                    return $grade_level_name;
                }
            ),
            array(
                "db" => "id",
                "dt" => 3,
                "formatter" => function ($d, $row) {
                    //$enrol = new Enrollments();
                    $count = 0;//$enrol->asObject()->where(['sectionid' => $row['id'], 'schoolyearid' => get_settings()->schoolyear])->countAllResults();
                    return '<div class="btn-group">
                        <a href="' . route_to('view-enrolled-students') . '/?id=' . $row['id'] . '&sid=' . get_settings()->schoolyear . '" class="btn btn-sm btn-link mx-1 viewEnrolledBtn" data-id="' . $row['id'] . '">' . $count . '</a>
                    </div>';
                }
            ),
            array(
                "db" => "id",
                "dt" => 4,
                "formatter" => function ($d, $row) {
                    return '<div class="btn-group">
                        <button class="btn btn-sm btn-link mx-1 editSectionBtn" data-id="' . $row['id'] . '"><i class="icon-copy dw dw-edit-file"></i></button>
                        <button class="btn btn-sm btn-link mx-1 deleteSectionBtn" data-id="' . $row['id'] . '"><i class="icon-copy dw dw-delete-2"></i></button>
                    </div>';
                },
            )
        );

        return json_encode(
            SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns)
        );
    }

    function postSection()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();

            $this->validate(
                [
                    'section_name' => [
                        'rules' => 'required',
                        'errors' => [
                            'required' => 'Section is required!'
                        ]
                    ],
                    'parent_grade_level' => [
                        'rules' => 'required',
                        'errors' => [
                            'required' => 'Grade Level is required'
                        ]
                    ]

                ]
            );

            if ($validation->run() === false) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $errors]);
            } else {
                $data = [
                    'name' => $request->getVar('section_name'),
                    'grade_level_id' => $request->getVar('parent_grade_level'),

                ];
                $section = new Sections();
                $result = $section->insert($data);

                if ($result) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'New Section Added!']);
                } else {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Something went Wrong!']);
                }
            }
        }
    }
    function getSection()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $id = $request->getVar('section_id');
            $section = new Sections();
            $result = $section->find($id);
            if ($result) {
                return $this->response->setJSON(['data' => $result]);
            }
        }
    }


    function updateSection()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            $id = $request->getVar('section_id');
            $this->validate([
                'section_name' => [
                    'rules' => 'required|is_unique[sections.name,id,' . $id . ']',
                    'errors' => [
                        'required' => 'Section must not empty',
                        'is_unique' => 'This name already exist',
                    ]
                ]
            ]);

            if ($validation->run() === FALSE) {
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $validation->getErrors()]);
            } else {
                $section = new Sections();
                $update = $section->where('id', $id)
                    ->set(['name' => $request->getVar('section_name')])
                    ->update();
                if ($update) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Section updated successfully!']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong while updating!']);
                }
            }

        }
    }

    function deleteSection()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $id = $request->getVar('section_id');
            $section = new Sections();
            $delete = $section->where('id', $id)->delete();

            if ($delete) {
                return $this->response->setJSON(['status' => 1, 'msg' => 'Section deleted successfully!']);
            } else {
                return $this->response->setJSON(['status' => 0, 'msg' => 'Action Failed!']);
            }
        }
    }
}
