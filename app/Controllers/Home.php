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
        return view('backend/pages/admin/home', $data);
    }
}
