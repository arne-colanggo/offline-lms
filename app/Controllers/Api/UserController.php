<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\AuthenticationServices;
use App\Controllers\Api\AuthController;
use App\Models\ProfileModel;
use App\Models\UsersModel;

class UserController extends BaseController
{
    protected AuthenticationServices $Auth;
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    public function __construct()
    {
        $this->Auth = new AuthenticationServices();
    }
    public function index()
    {
        $data = $this->Auth::id();

        return $this->response->setJSON([$data]);
    }
    public function userProfile()
    {

        $profile = new AuthController();
        $data = [
            'pageTitle' => 'User Profile',
            'profile' => $profile->profile()
        ];
        return view('backend/pages/users/profile', $data);
    }

    public function updateProfile()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            $this->validate([
                'id' => 'permit_empty|integer',
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
                'email' => [
                    'rules' => 'valid_email|is_unique[users.email,id,{id}]',
                    'errors' => [
                        'valid_email' => 'You entered an invalid email',
                        'is_unique' => 'Email already exists'
                    ]
                ]
            ]);

        }
        if ($this->validator->run() === false) {
            return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'errors' => $this->validator->getErrors()]);
        } else {
            $profile = new ProfileModel();
            $update = $profile->set([
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
            if ($update) {
                return $this->response->setJSON(['status' => 1, 'msg' => 'Update Success']);
            } else {
                return $this->response->setJSON(['status' => 0, 'msg' => 'Unable to update']);
            }
        }
    }

    public function updateProfilePicture()
    {
        $request = \Config\Services::request();
        $profile_id = $this->request->getVar('id');
        $profile = new ProfileModel();
        $profile_info = $profile->asObject()->where('id', $profile_id)->first();
        $path = "images/users/";
        $file = $request->getFile('profile_file');
        $old_picture = $profile_info->picture;
        $new_filename = 'UIMG_' . $profile_id . $file->getRandomName();


        // Image manipulation

        $upload_image = \Config\Services::image()
            ->withFile($file)
            ->resize(450, 450, true, 'height')
            ->save($path . $new_filename);

        if ($upload_image) {

            if ($old_picture != null && file_exists($path . $old_picture)) {
                unlink($path . $old_picture);
            }
            $profile->where('id', $profile_info->id)
                ->set(['picture' => $new_filename])
                ->update();

            echo json_encode(['status' => 1, 'msg' => 'Done! your profile picture has been updated successfully.']);

        } else {
            echo json_encode(['status' => 0, 'msg' => 'Something went wrong!']);
        }

    }
    public function settings()
    {
        $data = [
            'pageTitle' => 'Settings'
        ];
        return view('backend/pages/admin/settings', $data);
    }

    public function changeCredentials()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $session = session();
            $user = $session->get('userdata');
            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'You are not logged in.'
                ]);
            }

            $currentPassword = $this->request->getVar('current_password');
            $newUsername = trim($this->request->getVar('new_username'));
            $newPassword = $this->request->getVar('new_password');
            $confirmPassword = $this->request->getVar('confirm_password');

            if (!$currentPassword) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Current password is required.' . $currentPassword
                ]);
            }

            $userModel = new UsersModel();

            $dbUser = $userModel->find($user['id']);

            if (!$dbUser) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User account was not found.'
                ]);
            }

            /*
             * Verify current password
             */
            if (!password_verify($currentPassword, $dbUser['password'])) {

                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Current password is incorrect.'
                ]);
            }

            /*
             * Require at least one change
             */
            if ($newUsername === '' && $newPassword === '') {

                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Please provide a new username or password.'
                ]);
            }

            $updateData = [];

            /*
             * Change username
             */
            if ($newUsername !== '') {

                $existingUser = $userModel
                    ->where('username', $newUsername)
                    ->where('id !=', $user['id'])
                    ->first();

                if ($existingUser) {

                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Username is already being used.'
                    ]);
                }

                $updateData['username'] = $newUsername;
            }

            /*
             * Change password
             */
            if ($newPassword !== '') {

                if (strlen($newPassword) < 8) {

                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Password must be at least 8 characters.'
                    ]);
                }

                if ($newPassword !== $confirmPassword) {

                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'New passwords do not match.'
                    ]);
                }

                $updateData['password'] = password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );
            }

            /*
             * Update database
             */
            $userModel->update($user['id'], $updateData);

            /*
             * Update session username if changed
             */
            if (isset($updateData['username'])) {

                $user['username'] = $updateData['username'];

                $session->set('userdata', $user);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Your account credentials have been successfully updated.'
            ]);

        }
    }
}
