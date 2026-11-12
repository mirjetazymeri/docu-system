<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Branch;
use DataTables;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Department::with('branch')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('branch_name', function($row) {
                    return $row->branch->name;
                })
                ->addColumn('actions', function($row) {
                    return view('admin.departments.actions', compact('row'))->render();
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.departments.index');
    }

    public function create()
    {
        $branches = Branch::all();
        return view('admin.departments.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'head' => 'nullable|string|max:255',
        ]);

        Department::create($request->all());

        return redirect()->route('admin.departments.index')->with('success', __('ui.department_created_successfully'));
    }

    public function edit(Department $department)
    {
        $branches = Branch::all();
        return view('admin.departments.edit', compact('department', 'branches'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'head' => 'nullable|string|max:255',
        ]);

        $department->update($request->all());

        return redirect()->route('admin.departments.index')->with('success', __('ui.department_updated_successfully'));
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('admin.departments.index')->with('success', __('ui.department_deleted_successfully'));
    }
}
