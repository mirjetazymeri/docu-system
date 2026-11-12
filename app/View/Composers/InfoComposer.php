<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\Employee;
use App\Models\Contract;
use App\Models\Application;
use App\Models\User;
use App\Models\Setting;

class InfoComposer
{
    public function compose(View $view)
    {
        $totalEmployees = Employee::count();
        $totalApplications = Application::count();
        $totalContracts = Contract::count();
        $totalUsers = User::count();
        $appName = Setting::where('key', 'app_name')->value('value');

        $view->with([
            'totalEmployees' => $totalEmployees,
            'totalApplications' => $totalApplications,
            'totalContracts' => $totalContracts,
            'totalUsers' => $totalUsers,
            'appName' => $appName,
        ]);
    }
}
