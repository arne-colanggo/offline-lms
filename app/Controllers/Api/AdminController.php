<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SettingsModel;

class AdminController extends BaseController
{
    protected $db;

    public function __construct()
    {
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
}
