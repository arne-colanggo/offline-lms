<?php

namespace App\Controllers;

class Home extends BaseController
{
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    public function index(): string
    {
        $data = [
            'pageTitle' => 'Home'
        ];
        $session = session();

        if ($session->get('userdata')) {

            return view('backend/pages/admin/home', $data);

        } else {
            return view('backend/pages/auth/login', $data);
        }

    }
}
