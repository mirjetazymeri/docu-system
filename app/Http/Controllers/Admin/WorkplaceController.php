<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Workplace;
use App\Models\Department;
use App\Models\Branch;
use App\Models\Company;
use DataTables;

class WorkplaceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Workplace::with('department')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('department_name', function($row) {
                    return $row->department->name;
                })
                ->addColumn('branch_name', function($row) {
                    return $row->department->branch->name;
                })
                ->addColumn('actions', function($row) {
                    return view('admin.workplaces.actions', compact('row'))->render();
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.workplaces.index');
    }

    public function create()
    {
        $departments = Department::all();
        return view('admin.workplaces.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        Workplace::create($request->all());

        return redirect()->route('admin.workplaces.index')->with('success', __('ui.workplace_created_successfully'));
    }

    public function edit(Workplace $workplace)
    {
        $departments = Department::all();
        return view('admin.workplaces.edit', compact('workplace', 'departments'));
    }

    public function update(Request $request, Workplace $workplace)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $workplace->update($request->all());

        return redirect()->route('admin.workplaces.index')->with('success', __('ui.workplace_updated_successfully'));
    }

    public function destroy(Workplace $workplace)
    {
        $workplace->delete();
        return redirect()->route('admin.workplaces.index')->with('success', __('ui.workplace_deleted_successfully'));
    }
}
