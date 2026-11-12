<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.index', [
            'totalUsers' => User::count(),
            'totalApplications' => Application::count(),
        ]);
    }
}
