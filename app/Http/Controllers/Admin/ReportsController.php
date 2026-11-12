<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Employee;

class ReportsController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function vehiclesReport(Request $request)
    {
        $vehicles = Vehicle::all();

        // Generate the report logic (e.g., filter, summarize data, etc.)
        // For demonstration purposes, we're returning all vehicles
        return view('admin.reports.vehicles', compact('vehicles'));
    }

    public function employeesReport(Request $request)
    {
        $employees = Employee::with('user')->get();

        // Generate the report logic (e.g., filter, summarize data, etc.)
        // For demonstration purposes, we're returning all employees
        return view('admin.reports.employees', compact('employees'));
    }
}
