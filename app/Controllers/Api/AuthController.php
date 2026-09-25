<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\ProfileModel;
use App\Models\RevokeTokensModel;
use App\Libraries\AuthenticationServices;
use App\Libraries\Hash;

class AuthController extends BaseController
{
    protected UsersModel $userModel;
    protected AuthenticationServices $AuthServices;
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    protected $db;
    public function __construct()
    {
        $this->userModel = new UsersModel();
        $this->AuthServices = new AuthenticationServices();
        $this->db = db_connect();
    }

    public function unauthorized()
    {
        return $this->response
            ->setStatusCode(401)
            ->setJSON([
                'success' => false,
                'message' => 'Invalid Email or Password'
            ]);
    }
    public function loginform()
    {
        $data = [
            'pageTitle' => 'Login',
            'validation' => null
        ];

        return view('backend/pages/auth/login', $data);
    }
    public function loginhandler_bck()
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid JSON request'
                ]);
        }

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if ($email === '' || $password === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Email and password are required'
                ]);
        }

        $user = $this->userModel
            ->where('email', $email)
            ->where('status', 1)
            ->first();

        if (!$user) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ]);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid email or password',
                ]);
        }
        $this->AuthServices::setAuthorized($user);
        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'success' => true,
                'message' => 'Login successful',

                'data' => [
                    $user
                ]
            ]);
        //return redirect()->to('api/users');
    }

    public function loginHandler()
    {

        $user_type = $this->request->getVar('options');

        $fieldType = filter_var($this->request->getVar('login_id'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';


        if ($user_type == 'student') {
            $isValid = $this->validate([
                'login_id' => [
                    'rules' => 'required|is_not_unique[students.lrn]',
                    'errors' => [
                        'required' => 'Username is required!',
                        'is_not_unique' => 'No account found with this provided Username.'
                    ]
                ],
                'password' => [
                    'rules' => 'required|min_length[4]|max_length[45]',
                    'errors' => [
                        'required' => 'Password is required!',
                        'min_length' => 'The password must have at least 4 characters.',
                        'max_length' => 'The password cannot exceed 45 characters.'
                    ]
                ],
            ]);
        } else if ($user_type == "exam") {
            $isValid = $this->validate([
                'login_id' => [
                    'rules' => 'required|is_not_unique[students.lrn]',
                    'errors' => [
                        'required' => 'Username is required!',
                        'is_not_unique' => 'No account found with this provided Username.'
                    ]
                ],
                'examcode' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Exam Code is required'
                    ]
                ],
                'password' => [
                    'rules' => 'required|min_length[4]|max_length[45]',
                    'errors' => [
                        'required' => 'Password is required!',
                        'min_length' => 'The password must have at least 4 characters.',
                        'max_length' => 'The password cannot exceed 45 characters.'
                    ]
                ],
            ]);

        } else {
            if ($fieldType == 'email') {
                $isValid = $this->validate([
                    'login_id' => [
                        'rules' => 'required|valid_email|is_not_unique[users.email]',
                        'errors' => [
                            'required' => 'Email is required!',
                            'valid_email' => 'Please check the email field it does not appears to be valid.',
                            'is_not_unique' => 'No account found with this provided Email.'
                        ]
                    ],
                    'password' => [
                        'rules' => 'required|min_length[4]|max_length[45]',
                        'errors' => [
                            'required' => 'Password is required!',
                            'min_length' => 'The password must have at least 4 characters.',
                            'max_length' => 'The password cannot exceed 45 characters.'
                        ]
                    ],
                ]);
            } else {
                $isValid = $this->validate([
                    'login_id' => [
                        'rules' => 'required|is_not_unique[users.username]',
                        'errors' => [
                            'required' => 'Username is required!',
                            'is_not_unique' => 'No account found with this provided Username.'
                        ]
                    ],
                    'password' => [
                        'rules' => 'required|min_length[4]|max_length[45]',
                        'errors' => [
                            'required' => 'Password is required!',
                            'min_length' => 'The password must have at least 4 characters.',
                            'max_length' => 'The password cannot exceed 45 characters.'
                        ]
                    ],
                ]);
            }

        }

        if (!$isValid) {
            if ($user_type == "exam") {
                return view('backend/pages/auth/loginexam', [
                    'pageTitle' => 'Login',
                    'validator' => $this->validator
                ]);

            } else {

                return view('backend/pages/auth/login', [
                    'pageTitle' => 'Login',
                    'validator' => $this->validator
                ]);
            }
        } else {

            $username = $this->request->getVar('login_id') ?? '';
            $password = $this->request->getVar('password') ?? '';

            if ($username === '' || $password === '') {
                return $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Username and password are required'
                    ]);
            }

            $user = $this->userModel
                ->where($fieldType, $username)
                ->where('status', 1)
                ->first();

            if (!$user) {
                return redirect()->route('login')->with('fail', 'Invalid Username or Email');
            }

            if (!password_verify($password, $user['password'])) {
                return redirect()->route('login')->with('fail', 'Wrong password');
            }
            $this->AuthServices::setAuthorized($user);
            // Check if the user role [admin, teacher, student]
            if ($user['role'] === 'admin') {
                return redirect()->route('admin.dashboard');

            } else if ($user['role'] === 'teacher') {
                //Go to Dashboard teacher

            } else {
                //Go to Dashboard student

            }
        }
    }

    public function profile()
    {

        $user = session('userdata');
        $user_profile = new ProfileModel();
        $profile_data = $user_profile->asObject()->where('id', $user['id'])->first();
        $profile_data->userdata = $user;
        $profile_data->address = get_address($profile_data->addressid);
        return $profile_data;


    }
    public function logout()
    {
        $this->AuthServices::forget();
        return redirect()->route('/')->with('fail', 'User has been logout');

    }
    public function logoutJWT()
    {
        $header = $this->request->getHeaderLine('Authorization');

        if (!$header) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Authorization token is required.'
                ]);
        }

        if (!preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid authorization header.'
                ]);
        }

        $token = $matches[1];

        try {

            $decoded = $this->jwtService->validate($token);

            if (!isset($decoded->jti)) {
                return $this->response
                    ->setStatusCode(401)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Invalid token.'
                    ]);
            }

            $revokedTokenModel = new RevokeTokensModel();

            // Prevent duplicate revocation
            $existing = $revokedTokenModel
                ->where('jti', $decoded->jti)
                ->first();

            if (!$existing) {

                $revokedTokenModel->insert([
                    'jti' => $decoded->jti,
                    'user_id' => $decoded->user->id,
                    'expires_at' => date(
                        'Y-m-d H:i:s',
                        $decoded->exp
                    ),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            return $this->response
                ->setStatusCode(200)
                ->setJSON([
                    'success' => true,
                    'message' => 'Logout successful.'
                ]);

        } catch (\Throwable $e) {

            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid or expired token.'
                ]);
        }
    }
}