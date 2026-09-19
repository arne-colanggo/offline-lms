<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\AuthenticationServices;

class UserController extends BaseController
{
    protected AuthenticationServices $Auth;
    public function __construct()
    {
        $this->Auth = new AuthenticationServices();
    }
    public function index()
    {
        $data = $this->Auth::id();

        return $this->response->setJSON([$data]);
    }
}
