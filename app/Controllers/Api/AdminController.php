<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SchoolYear;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SettingsModel;
use App\Models\GradeLevel;
use App\Models\Sections;
use App\Models\Subjects;
use App\Models\Enrollment;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared;

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
                        <button class="btn btn-sm btn-link mx-1 editSchoolYearBtn" data-id="' . $row['id'] . '"><i class="icon-copy dw dw-edit-file"></i></button>
                        <button class="btn btn-sm btn-link mx-1 deleteSchoolYearBtn" data-id="' . $row['id'] . '"><i class="icon-copy dw dw-delete-2"></i></button>
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
                        <button class="btn btn-sm btn-link mx-1 editGradeLevelBtn" data-id="' . $row['id'] . '"><i class="icon-copy dw dw-edit-file"></i></button>
                        <button class="btn btn-sm btn-link mx-1 deleteGradeLevelBtn" data-id="' . $row['id'] . '"><i class="icon-copy dw dw-delete-2"></i></button>
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
                    $enrol = new Enrollment();
                    $count = $enrol->asObject()->where(['sectionid' => $row['id'], 'schoolyearid' => get_settings()->schoolyear])->countAllResults();
                    return '<div class="btn-group">
                        <a href="' . route_to('view.enrolled.students') . '/?id=' . $row['id'] . '&sid=' . get_settings()->schoolyear . '" class="btn btn-sm btn-link mx-1 viewEnrolledBtn" data-id="' . $row['id'] . '">' . $count . '</a>
                    </div>';
                }
            ),
            array(
                "db" => "id",
                "dt" => 4,
                "formatter" => function ($d, $row) {
                    return '<div class="btn-group">
                    
                    <a class="btn btn-sm btn-link mx-1" href="' . route_to('section.dashboard') . '/?id=' . $row['id'] . '"><i class="icon-copy dw dw-monitor"></i></button></a>
                        <button class="btn btn-sm btn-link mx-1 editSectionBtn" data-id="' . $row['id'] . '"><i class="icon-copy dw dw-edit-2"></i></button>
                        <button class="btn btn-sm btn-link mx-1 deleteSectionBtn" data-id="' . $row['id'] . '"><i class="icon-copy dw dw-delete-3"></i></button>
                    </div>';
                },
            ),
            array(
                "db" => "id",
                "dt" => 5,
                "formatter" => function ($d, $row) {
                    return '<div><a class="btn btn-sm btn-link mx-1" href="' . route_to('section.dashboard') . '/?id=' . $row['id'] . '"><i class="icon-copy dw dw-edit-file"></i></button></a></div>';
                }
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

    //Subject
    function subject()
    {
        $data = [
            'pageTitle' => 'Subject',
        ];

        return view('backend/pages/admin/subject', $data);
    }

    function getSubjects()
    {
        $gradelevel = new GradeLevel();
        $section = new Sections();
        $dbDetails = array(
            "host" => $this->db->hostname,
            "user" => $this->db->username,
            "pass" => $this->db->password,
            "db" => $this->db->database,
        );

        $table = "subjects";
        $primaryKey = "id";
        $joinQuery = "FROM subjects s JOIN gradelevels g ON s.grade_level_id = g.id";

        $columns = array(
            array(
                "db" => "s.id",
                "dt" => 0,
                "field" => "subjectid",
                "as" => "subjectid"
            ),
            array(
                "db" => "s.name",
                "dt" => 1,
                "as" => "subjectname",
                "field" => "subjectname"
            ),
            array(
                "db" => "g.name",
                "dt" => 2,
                "field" => "gradelevelname",
                "as" => "gradelevelname"
            ),

            array(
                "db" => "s.id",
                "dt" => 3,
                "field" => "subjectid",
                "as" => "subjectid",
                "formatter" => function ($d, $row) {
                    return '<div class="btn-group">
                        <button class="btn btn-sm btn-link mx-1 editSubjectBtn" data-id="' . $row['subjectid'] . '"><i class="icon-copy dw dw-edit-file"></i></button>
                        <button class="btn btn-sm btn-link mx-1 deleteSubjectBtn" data-id="' . $row['subjectid'] . '"><i class="icon-copy dw dw-delete-2"></i></button>
                    </div>';
                },
            )
        );

        return json_encode(
            SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns, $joinQuery)
        );
    }

    function postSubject()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();

            $this->validate(
                [
                    'subject_name' => [
                        'rules' => 'required|is_unique[subjects.name]',
                        'errors' => [
                            'required' => 'Subject is required!',
                            'is_unique' => 'This subject already exists!'
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
                    'name' => $request->getVar('subject_name'),
                    'grade_level_id' => $request->getVar('parent_grade_level'),
                    'description' => $request->getVar('description'),

                ];
                $subject = new Subjects();
                $result = $subject->insert($data);

                if ($result) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'New Subject Added!']);
                } else {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Something went Wrong!']);
                }
            }
        }
    }

    function getSubject()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $id = $request->getVar('subject_id');
            $subject = new Subjects();
            $result = $subject->find($id);
            if ($result) {
                return $this->response->setJSON(['data' => $result]);
            }
        }
    }


    function updateSubject()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            $id = $request->getVar('subject_id');
            $this->validate([
                'subject_name' => [
                    'rules' => 'required|is_unique[subjects.name,id,' . $id . ']',
                    'errors' => [
                        'required' => 'Section must not empty',
                        'is_unique' => 'This name already exist',
                    ]
                ]
            ]);

            if ($validation->run() === FALSE) {
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $validation->getErrors()]);
            } else {
                $subject = new Subjects();
                $update = $subject->where('id', $id)
                    ->set(['name' => $request->getVar('subject_name'), 'description' => $request->getVar('description'), 'grade_level_id' => $request->getVar('parent_grade_level')])
                    ->update();
                if ($update) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Subject updated successfully!']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong while updating!']);
                }
            }

        }
    }

    function deleteSubject()
    {
        $request = \Config\Services::request();
        if ($request->isAJAX()) {
            $id = $request->getVar('subject_id');
            $subject = new Subjects();
            $delete = $subject->where('id', $id)->delete();

            if ($delete) {
                return $this->response->setJSON(['status' => 1, 'msg' => 'Subject deleted successfully!']);
            } else {
                return $this->response->setJSON(['status' => 0, 'msg' => 'Action Failed!']);
            }
        }
    }

    function getParentSubjects()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $id = $request->getVar('subjectid');
            $subject = new Subjects();
            $options = '';
            $parent_subjects = $subject->findAll();

            if (count($parent_subjects)) {
                $added_options = '<option value="">Select</option>';
                foreach ($parent_subjects as $parent_subject) {
                    $isSelected = $parent_subject['id'] == $id ? 'selected' : '';
                    $added_options .= '<option value="' . $parent_subject['id'] . '" ' . $isSelected . ' >' . $parent_subject['name'] . '</option>';
                }

                $options = $options . $added_options;

                return $this->response->setJSON(['status' => 1, 'data' => $options]);

            } else {
                return $this->response->setJSON(['status' => 0, 'data' => $options]);
            }

        }
    }

    function getSubjectsPerGrade()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $id = $request->getVar('subjectid');
            $sectionid = $request->getVar('sectionid');
            $section = new Sections();
            $gradeid = $section->asObject()->where('id', $sectionid)->first();
            $subject = new Subjects();
            $options = '';
            $parent_subjects = $subject->where('grade_level_id', $gradeid->grade_level_id)->find();

            if (count($parent_subjects)) {
                $added_options = '<option value="">Select</option>';
                foreach ($parent_subjects as $parent_subject) {
                    $isSelected = $parent_subject['id'] == $id ? 'selected' : '';
                    $added_options .= '<option value="' . $parent_subject['id'] . '" ' . $isSelected . ' >' . $parent_subject['name'] . '</option>';
                }

                $options = $options . $added_options;

                return $this->response->setJSON(['status' => 1, 'data' => $options]);

            } else {
                return $this->response->setJSON(['status' => 0, 'data' => $options]);
            }

        }
    }
    public function viewEnrolledStudents()
    {
        $request = \Config\Services::request();
        $section = new Sections();
        $sy = new SchoolYear();
        $date_now = Carbon::now();
        $section_data = $section->asObject()->where('id', $request->getVar('id'))->first();
        $data = [
            'pageTitle' => 'View Enrolled Students',
            'sectionid' => $request->getVar('id'),
            'schoolyear' => $sy->asObject()->where('id', get_settings()->schoolyear)->first(),
            'section' => $section_data,
            'schoolyearid' => get_settings()->schoolyear,
            'date_now' => $date_now,
        ];

        return view('backend/pages/admin/viewenrolledstudents', $data);
    }

    public function sectionDashboard()
    {
        $pageTitle = "Section Dashboard";
        $db = \Config\Database::connect();
        $sectionId = (int) $this->request->getGet('id');
        $threshold = (int) ($this->request->getGet('threshold') ?? 5);

        $section = $db->table('sections sec')
            ->select('sec.id, sec.name, sec.grade_level_id, g.name grade_name')
            ->join('gradelevels g', 'g.id = sec.grade_level_id')
            ->where('sec.id', $sectionId)->get()->getRow();

        if (!$section) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Section not found.');
        }

        $monthStart = date('Y-m-01');
        $schoolYearStart = (date('n') >= 6 ? date('Y') : date('Y') - 1) . '-06-01';

        $daily = "(SELECT studentid, classdate,
                  SUM(attendancestatus='P') p, SUM(attendancestatus='L') l,
                  SUM(attendancestatus='A') a, COUNT(*) n
           FROM attendances WHERE classdate >= " . $db->escape($schoolYearStart) . " AND schoolyearid=" . get_settings()->schoolyear . "
           GROUP BY studentid, classdate)";

        // One row per enrolled learner in this section, with attendance totals for the school year

        $learners = $db->table('students s')
            ->select("s.id, s.lrn, p.firstname, p.lastname, p.gender,
              COALESCE(SUM(d.a < d.n),0) present,
              COALESCE(SUM(d.a = d.n),0) absent,
              COALESCE(SUM(d.l > 0),0)   late,
              COUNT(d.classdate) days", false)
            ->join("$daily d", 'd.studentid = s.id', 'left', false)
            ->join('profiles p', 'p.id = s.profile_id')
            ->join('enrollments e', 's.id = e.studentid')
            ->where('e.sectionid', $sectionId)->where('e.schoolyearid', get_settings()->schoolyear)
            ->groupBy('s.id')->orderBy('p.lastname')->orderBy('p.firstname')
            ->get()->getResult();

        // $learners = $db->table('students s')
        //     ->select("s.id, s.lrn, p.firstname, p.lastname, p.gender,
        //           COALESCE(SUM(a.attendancestatus='P'),0) present,
        //           COALESCE(SUM(a.attendancestatus='A'),0)  absent,
        //           COALESCE(SUM(a.attendancestatus='L'),0)    late,
        //           COUNT(a.id) days", false)
        //     ->join('attendances a', 'a.studentid = s.id AND a.classdate >= ' . $db->escape($schoolYearStart), 'left')->join('enrollments e', 'a.studentid=s.id AND e.schoolyearid=' . get_settings()->schoolyear)->join('profiles p', 'p.id=s.profile_id')
        //     ->where('e.sectionid', $sectionId)
        //     ->groupBy('s.id')
        //     ->orderBy('p.lastname')->orderBy('p.firstname')
        //     ->get()->getResult();

        $totalEnrolled = count($learners);
        $male = count(array_filter($learners, fn($l) => $l->gender === 'M'));
        $female = count(array_filter($learners, fn($l) => $l->gender === 'F'));

        // Learners with frequent absences
        $frequent = array_values(array_filter($learners, fn($l) => $l->absent >= $threshold));

        // Absence frequency bands (learners with at least one absence)
        $bands = ['1-2' => 0, '3-4' => 0, '5-9' => 0, '10+' => 0];
        foreach ($learners as $l) {
            if ($l->absent == 0)
                continue;
            if ($l->absent <= 2)
                $bands['1-2']++;
            elseif ($l->absent <= 4)
                $bands['3-4']++;
            elseif ($l->absent <= 9)
                $bands['5-9']++;
            else
                $bands['10+']++;
        }

        // Attendance rate this month and last attendance date (this section only)
        $base = fn() => $db->table('attendances a')
            ->join('students s', 's.id = a.studentid')
            ->join('enrollments e', 'e.studentid = s.id')
            ->where('e.sectionid', $sectionId)
            ->where('e.schoolyearid', get_settings()->schoolyear);

        $m = $base()->select("COALESCE(SUM(a.attendancestatus='P'),0) p, COUNT(*) t", false)
            ->where('a.classdate >=', $monthStart)->get()->getRow();
        $attendanceRate = $m->t > 0 ? round($m->p / $m->t * 100, 1) : 0;

        $lastDate = $base()->selectMax('a.classdate', 'last_date')->get()->getRow()->last_date ?? null;

        // Monthly trend
        $trend = $base()
            ->select("DATE_FORMAT(a.classdate,'%b %Y') label, ROUND(SUM(a.attendancestatus='P')/COUNT(*)*100,1) rate", false)
            ->where('a.classdate >=', $schoolYearStart)->where('a.schoolyearid', get_settings()->schoolyear)
            ->groupBy("DATE_FORMAT(a.classdate,'%Y-%m')")->orderBy("DATE_FORMAT(a.classdate,'%Y-%m')")
            ->get()->getResult();

        // Absences by weekday (MySQL DAYOFWEEK: 1 = Sunday)
        $dowRows = $base()->select('DAYOFWEEK(a.classdate) dow, COUNT(*) n', false)
            ->where('a.attendancestatus', 'A')->where('a.classdate >=', $schoolYearStart)->where('a.schoolyearid', get_settings()->schoolyear)
            ->groupBy('DAYOFWEEK(a.classdate)')->get()->getResult();
        $weekday = ['Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0];
        $map = [2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri'];
        foreach ($dowRows as $r) {
            if (isset($map[$r->dow]))
                $weekday[$map[$r->dow]] = (int) $r->n;
        }


        return view('backend/pages/admin/sectiondashboard', compact(
            'section',
            'learners',
            'totalEnrolled',
            'male',
            'female',
            'frequent',
            'bands',
            'attendanceRate',
            'lastDate',
            'trend',
            'weekday',
            'threshold',
            'pageTitle'
        ));
    }
    public function uploadAttendance()
    {
        $rules = [
            'attendance_file' => 'uploaded[attendance_file]|ext_in[attendance_file,xls,xlsx]',
            'section_id' => 'required|is_natural_no_zero',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['token' => csrf_hash(), 'error' => $this->validator->getErrors(), 'status' => 0]);
        }

        $db = \Config\Database::connect();
        $sectionId = (int) $this->request->getPost('section_id');
        $file = $this->request->getFile('attendance_file');

        try {
            $sheet = IOFactory::load($file->getTempName())->getActiveSheet();
        } catch (\Throwable $e) {
            return $this->response->setJSON(['token' => csrf_hash(), 'error' => [], 'status' => 0, 'msg' => 'Unreadable Excel file.']);
        }

        // ---- Date: from cell B2 if present, else from the form's date field ----
        $dateCell = $sheet->getCell('B2');
        $date = null;
        if ($dateCell->getValue() !== null && $dateCell->getValue() !== '') {
            if (Shared\Date::isDateTime($dateCell)) {
                $date = Shared\Date::excelToDateTimeObject($dateCell->getValue())->format('Y-m-d');
            } elseif (strtotime((string) $dateCell->getValue())) {
                $date = date('Y-m-d', strtotime((string) $dateCell->getValue()));
            }
        }
        $date = $date ?? $this->request->getPost('date');
        if (!$date || !strtotime($date)) {
            return $this->response->setJSON(['token' => csrf_hash(), 'error' => ['date' => 'Date not found in cell B2. Please choose one.'], 'status' => 0]);
        }

        // ---- Period columns: row 6, from column D until the first empty header ----
        $headerRow = 6;
        $firstDataRow = 7;
        $firstPeriodCol = 4; // D
        $periods = [];
        for ($c = $firstPeriodCol; ; $c++) {
            $cell = $sheet->getCell([$c, $headerRow]);
            $v = $cell->getValue();
            if ($v === null || $v === '')
                break;

            if (is_numeric($v)) {
                $secs = (int) round(fmod((float) $v, 1) * 86400);
            } else {
                $t = strtotime((string) $v);
                if (!$t)
                    continue;
                $secs = (int) date('G', $t) * 3600 + (int) date('i', $t) * 60;
            }
            if ($secs < 7 * 3600)
                $secs += 12 * 3600;   // 1:45 -> 13:45
            $periods[$c] = gmdate('H:i:s', $secs);
        }
        if (!$periods) {
            return $this->response->setJSON(['token' => csrf_hash(), 'error' => [], 'status' => 0, 'msg' => 'No period columns found in row 6.']);
        }

        // ---- Students of this section, keyed by LRN ----
        $students = [];
        foreach ($db->table('students s')->select('s.id, s.lrn')->join('enrollments e', 'e.studentid = s.id')->where('e.sectionid', $sectionId)->where('e.schoolyearid', get_settings()->schoolyear)->get()->getResult() as $s) {
            $students[trim((string) $s->lrn)] = $s->id;
        }

        $map = ['P' => 'P', 'A' => 'A', 'L' => 'L'];
        $rows = [];
        $unknownLrn = [];
        $badCodes = 0;

        for ($r = $firstDataRow, $last = $sheet->getHighestDataRow(); $r <= $last; $r++) {
            $lrn = trim((string) $sheet->getCell([2, $r])->getValue());
            if ($lrn === '')
                continue;
            if (!isset($students[$lrn])) {
                $unknownLrn[] = $lrn;
                continue;
            }

            foreach ($periods as $c => $time) {
                $code = strtoupper(trim((string) $sheet->getCell([$c, $r])->getValue()));
                if ($code === '')
                    continue;
                if (!isset($map[$code])) {
                    $badCodes++;
                    continue;
                }
                $rows[] = ['studentid' => $students[$lrn], 'classdate' => $date, 'classtime' => $time, 'attendancestatus' => $map[$code], 'sectionid' => $sectionId, 'schoolyearid' => get_settings()->schoolyear];
            }
        }

        if (!$rows) {
            return $this->response->setJSON(['token' => csrf_hash(), 'error' => [], 'status' => 0, 'msg' => 'No matching learners or attendance codes found.']);
        }

        // ---- Replace that day's attendance for this section only ----
        $db->transStart();
        $db->table('attendances')->where('classdate', $date)->whereIn('studentid', array_values($students))->delete();
        $db->table('attendances')->insertBatch($rows);
        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setJSON(['token' => csrf_hash(), 'error' => [], 'status' => 0, 'msg' => 'Database error. Nothing was saved.']);
        }

        $msg = 'Attendance for ' . date('M d, Y', strtotime($date)) . ' saved: ' . count($rows) . ' entries, ' . count($periods) . ' periods.';
        if ($unknownLrn)
            $msg .= ' Skipped ' . count($unknownLrn) . ' LRN(s) not in this section: ' . implode(', ', array_slice($unknownLrn, 0, 5)) . (count($unknownLrn) > 5 ? '...' : '');
        if ($badCodes)
            $msg .= ' Ignored ' . $badCodes . ' cell(s) that were not P, A or L.';

        return $this->response->setJSON(['token' => csrf_hash(), 'error' => [], 'status' => 1, 'msg' => $msg]);
    }

    public function index()
    {
        $db = db_connect();
        $threshold = max(1, (int) ($this->request->getGet('threshold') ?? 5));
        $today = date('Y-m-d');
        $monthFrom = date('Y-m-01');

        // ---- Headline numbers ----
        $totalEnrolled = $db->table('enrollments')->where('sectionid IS NOT NULL', null, false)->where('schoolyearid', get_settings()->schoolyear)->countAllResults();
        $totalGrades = $db->table('gradelevels')->countAllResults();
        $totalSections = $db->table('sections')->countAllResults();
        //$totalTeachers = $db->tableExists('teachers') ? $db->table('teachers')->countAllResults() : null;

        $genderRows = $db->table('students s')->select('p.gender, COUNT(*) AS total')->join('profiles p', 'p.id = s.profile_id')->join('enrollments e', 'e.studentid = s.id')
            ->where('e.sectionid IS NOT NULL', null, false)->where('e.schoolyearid', get_settings()->schoolyear)->groupBy('p.gender')->get()->getResult();
        $male = $female = 0;
        foreach ($genderRows as $g) {
            if (($g->gender) === 'M')
                $male = (int) $g->total;
            if (($g->gender) === 'F')
                $female = (int) $g->total;
        }

        // ---- Attendance today / this month ----
        $todayRow = $db->table('attendances')
            ->select("SUM(attendancestatus='P') AS present, SUM(attendancestatus='A') AS absent, SUM(attendancestatus='L') AS late, COUNT(*) AS total")
            ->where('classdate', $today)->where('schoolyearid', get_settings()->schoolyear)->get()->getRow();
        $presentToday = (int) ($todayRow->present ?? 0);
        $absentToday = (int) ($todayRow->absent ?? 0);
        $lateToday = (int) ($todayRow->late ?? 0);
        $todayTotal = (int) ($todayRow->total ?? 0);
        $rateToday = $todayTotal > 0 ? round(($presentToday + $lateToday) / $todayTotal * 100) : null;

        $monthRow = $db->table('attendances')
            ->select("SUM(attendancestatus IN ('P','L')) AS attended, COUNT(*) AS total")
            ->where('classdate >=', $monthFrom)->where('schoolyearid', get_settings()->schoolyear)->get()->getRow();
        $rateMonth = ($monthRow && $monthRow->total > 0) ? round($monthRow->attended / $monthRow->total * 100) : 0;

        $lastDate = $db->table('attendances')->selectMax('classdate', 'd')->get()->getRow()->d ?? null;

        // ---- Enrollment by grade level ----
        $byGrade = $db->table('gradelevels gl')
            ->select('gl.id, gl.name, COUNT(e.studentid) AS total')
            ->join('sections s', 's.grade_level_id = gl.id', 'left outer')
            ->join('enrollments e', 'e.sectionid = s.id', 'left outer')
            ->where('e.schoolyearid', get_settings()->schoolyear)
            ->groupBy('gl.id')->orderBy('gl.id')->get()->getResult();

        // ---- Sections overview ----
        $sections = $db->table('sections s')
            ->select('s.id, s.name, gl.name AS grade_name, COUNT(st.id) AS total')
            ->join('gradelevels gl', 'gl.id = s.grade_level_id')
            ->join('enrollments en', 'en.sectionid = s.id', 'left')
            ->join('students st', 'st.id = en.studentid')
            ->where('en.schoolyearid', get_settings()->schoolyear)
            ->groupBy('s.id')->orderBy('gl.id')->orderBy('s.name')->get()->getResult();

        // ---- Attendance trend (last 6 months) ----
        $trendRows = $db->table('attendances')
            ->select("DATE_FORMAT(classdate,'%Y-%m') AS ym, SUM(attendancestatus IN ('P','L')) AS attended, COUNT(*) AS total", false)
            ->where('classdate >=', date('Y-m-01', strtotime('-5 months')))
            ->where('schoolyearid', get_settings()->schoolyear)
            ->groupBy('ym')->orderBy('ym')->get()->getResult();
        $trend = array_map(fn($r) => [
            'label' => date('M Y', strtotime($r->ym . '-01')),
            'rate' => $r->total > 0 ? round($r->attended / $r->total * 100, 1) : 0,
        ], $trendRows);

        // ---- Absences by weekday (Mon-Fri) ----
        $weekday = ['Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0];
        $map = [2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri'];
        $wdRows = $db->table('attendances')
            ->select('DAYOFWEEK(classdate) AS dow, COUNT(*) AS total', false)
            ->where('attendancestatus', 'A')->where('classdate >=', date('Y-m-d', strtotime('-90 days')))
            ->where('schoolyearid', get_settings()->schoolyear)
            ->groupBy('dow')->get()->getResult();
        foreach ($wdRows as $r) {
            if (isset($map[$r->dow]))
                $weekday[$map[$r->dow]] = (int) $r->total;
        }

        // ---- Absences by grade level (this month) ----
        $absByGrade = $db->table('attendances a')
            ->select('gl.name, COUNT(*) AS total')
            ->join('sections s', 's.id = a.sectionid')
            ->join('gradelevels gl', 'gl.id = s.grade_level_id')
            ->where('a.attendancestatus', 'A')->where('a.classdate >=', $monthFrom)
            ->where('a.schoolyearid', get_settings()->schoolyear)
            ->groupBy('gl.id')->orderBy('gl.id')->get()->getResult();

        // ---- Frequent absentees (school-wide) ----
        $frequent = $db->table('attendances a')
            ->select('st.lrn, pr.firstname, pr.lastname, s.name AS section_name, gl.name AS grade_name, COUNT(*) AS absences')
            ->join('students st', 'st.id = a.studentid')
            ->join('profiles pr', 'pr.id = st.profile_id')
            ->join('sections s', 's.id = a.sectionid')
            ->join('gradelevels gl', 'gl.id = s.grade_level_id')
            ->where('a.attendancestatus', 'A')
            ->where('a.schoolyearid', get_settings()->schoolyear)
            ->groupBy('st.id')->having('absences >=', $threshold)
            ->orderBy('absences', 'DESC')->limit(8)->get()->getResult();

        $frequentCount = $db->query(
            "SELECT COUNT(*) AS c FROM (SELECT studentid FROM attendances WHERE attendancestatus='A' AND schoolyearid=" . get_settings()->schoolyear . "
             GROUP BY studentid HAVING COUNT(*) >= ?) t",
            [$threshold]
        )->getRow()->c ?? 0;

        // ---- Announcements & recent enrollees ----
        // $announcements = $db->tableExists('announcements')
        //     ? $db->table('announcements')->orderBy('is_pinned', 'DESC')->orderBy('created_at', 'DESC')->limit(5)->get()->getResult()
        //     : [];
        $announcements = [];
        $recent = $db->table('students st')
            ->select('st.lrn, p.firstname, p.lastname, p.created_at, s.name AS section_name, gl.name AS grade_name')
            ->join('profiles p', 'p.id = st.profile_id')
            ->join('enrollments en', 'en.studentid = st.id')
            ->join('sections s', 's.id = en.sectionid')
            ->join('gradelevels gl', 'gl.id = s.grade_level_id')
            ->orderBy('st.id', 'DESC')->limit(6)->get()->getResult();

        return view('backend/pages/admin/home', [
            'pageTitle' => 'Dashboard',
            'threshold' => $threshold,
            'totalEnrolled' => $totalEnrolled,
            'male' => $male,
            'female' => $female,
            'totalGrades' => $totalGrades,
            'totalSections' => $totalSections,
            'totalTeachers' => 0,
            'rateToday' => $rateToday,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'lateToday' => $lateToday,
            'rateMonth' => $rateMonth,
            'lastDate' => $lastDate,
            'byGrade' => $byGrade,
            'sections' => $sections,
            'trend' => $trend,
            'weekday' => $weekday,
            'absByGrade' => $absByGrade,
            'frequent' => $frequent,
            'frequentCount' => (int) $frequentCount,
            'announcements' => $announcements,
            'recent' => $recent,
        ]);
    }

    public function postAnnouncement()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $rules = [
            'title' => ['rules' => 'required|min_length[3]|max_length[150]', 'errors' => ['required' => 'Enter a title.']],
            'body' => ['rules' => 'required|min_length[5]', 'errors' => ['required' => 'Enter the announcement message.']],
            'audience' => ['rules' => 'required|in_list[All,Teachers,Students,Parents]', 'errors' => ['required' => 'Choose an audience.']],
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'status' => 0,
                'error' => $this->validator->getErrors(),
            ]);
        }

        $ok = db_connect()->table('announcements')->insert([
            'title' => $this->request->getPost('title'),
            'body' => $this->request->getPost('body'),
            'audience' => $this->request->getPost('audience'),
            'is_pinned' => $this->request->getPost('is_pinned') ? 1 : 0,
            'created_by' => session()->get('user_id'), // adjust to your auth session key
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'status' => $ok ? 1 : 0,
            'msg' => $ok ? 'Announcement posted.' : 'Could not post the announcement. Please try again.',
            'error' => [],
        ]);
    }

    public function deleteAnnouncement()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $id = (int) $this->request->getPost('id');
        $ok = $id > 0 && db_connect()->table('announcements')->where('id', $id)->delete();

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'status' => $ok ? 1 : 0,
            'msg' => $ok ? 'Announcement deleted.' : 'Could not delete the announcement.',
        ]);
    }
}
