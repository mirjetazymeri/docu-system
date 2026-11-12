<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Supplier;

class SearchController extends Controller
{
    public function get_users(Request $request)
    {
        $usersQuery = User::doesntHave('employee');

        if ($request->has('term')) {
            $usersQuery->where(function($query) use ($request) {
                $query->where('first_name', 'like', '%' . $request->term . '%')
                      ->orWhere('last_name', 'like', '%' . $request->term . '%');
            });
        }

        $users = $usersQuery->take(20)->get(['id', 'first_name', 'last_name']);
        // Format user data
        $users = $users->map(function($user) {
            $user->full_name = $user->first_name . ' ' . $user->last_name;
            return $user;
        });

        return response()->json($users);
    }

    public function get_employees(Request $request)
    {
        $employeesQuery = Employee::query();

        if ($request->has('term')) {
            $employeesQuery->where('full_name', 'like', '%' . $request->term . '%');
        }

        $employees = $employeesQuery->take(20)->get(['id', 'full_name']);

        return response()->json($employees);
    }

}
