<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $data = [
            'username'    => session()->get('username'),
            'role'        => session()->get('role'),
            'branch_name' => session()->get('branch_name'),
        ];
        return view('dashboard/index', $data);
    }
}
