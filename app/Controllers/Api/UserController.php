<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\AuthenticationServices;
use App\Controllers\Api\AuthController;

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
}
