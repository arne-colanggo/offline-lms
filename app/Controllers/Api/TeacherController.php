<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\AuthenticationServices;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;
use CassController;

class TeacherController extends BaseController
{
    protected UsersModel $userModel;
    protected AuthenticationServices $AuthServices;
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    protected $db;
    protected $class;
    public function __construct()
    {
        $this->userModel = new UsersModel();
        $this->AuthServices = new AuthenticationServices();
        $this->class = new ClassController();
        $this->db = db_connect();
    }
    public function index()
    {
        $advisory = $this->db->table('sections s')
            ->select('*')
            ->where('teacherid', $this->AuthServices::id())
            ->get()->getResultObject();
        $nonadvisory = $this->db->table('classes c')
            ->select('COUNT(*) AS totalclass')
            ->where('teacherid', $this->AuthServices::id())
            ->where('schoolyearid', get_settings()->schoolyear)
            ->get()->getResultObject();
        $pageTitle = 'Dashboard';

        return view('backend/pages/teacher/home', compact('pageTitle', 'advisory', 'nonadvisory'));
    }
}
