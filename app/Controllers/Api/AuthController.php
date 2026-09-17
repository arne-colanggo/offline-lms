<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Libraries\JWTService;

class AuthController extends BaseController
{
    protected UsersModel $userModel;
    protected JWTService $jwtService;

    public function __construct()
    {
        $this->userModel = new UsersModel();
        $this->jwtService = new JWTService();
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

        $token = $this->jwtService->generate($user);

        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'success' => true,
                'message' => 'Login successful',

                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => (int) env(
                        'JWT_EXPIRATION',
                        3600
                    ),

                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ]
            ]);
    }


    public function profile()
    {
        $user = $this->request->user;

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Authenticated user',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }
    public function veriyHash()
    {
        $hash = password_hash('1245', PASSWORD_BCRYPT);
        return $this->response->setJSON(['password' => $hash, 'verify' => password_verify('1245', $hash)]);
    }
}