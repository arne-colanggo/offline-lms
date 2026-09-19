<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\ProfileModel;
use App\Models\RevokeTokensModel;
use App\Libraries\AuthenticationServices;

class AuthController extends BaseController
{
    protected UsersModel $userModel;
    protected AuthenticationServices $AuthServices;

    public function __construct()
    {
        $this->userModel = new UsersModel();
        $this->AuthServices = new AuthenticationServices();
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
    public function login()
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


    public function profile()
    {
        $user = $this->request->user;
        $user_profile = new ProfileModel();
        $profile_data = $user_profile->asObject()->where('id', $user->id)->first();
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Authenticated user',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'profile' => $profile_data
            ]
        ]);
    }
    public function logout()
    {
        $this->AuthServices::forget();
        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'success' => true,
                'message' => 'Logout successful.'
            ]);
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