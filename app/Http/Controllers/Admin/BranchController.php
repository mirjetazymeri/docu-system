<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Company;
use DataTables;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Branch::with('company')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('company_name', function($row) {
                    return $row->company->name;
                })
                ->addColumn('actions', function($row) {
                    return view('admin.branches.actions', compact('row'))->render();
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.branches.index');
    }

    public function create()
    {
        $companies = Company::all();
        return view('admin.branches.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'manager' => 'required|string|max:255',
        ]);

        Branch::create($request->all());

        return redirect()->route('admin.branches.index')->with('success', __('ui.branch_created_successfully'));
    }

    public function edit(Branch $branch)
    {
        $companies = Company::all();
        return view('admin.branches.edit', compact('branch', 'companies'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'manager' => 'required|string|max:255',
        ]);

        $branch->update($request->all());

        return redirect()->route('admin.branches.index')->with('success', __('ui.branch_updated_successfully'));
    }

    public function destroy(Branch $branch)
    {
        // Optional: Check for departments or other entities before deleting
        if ($branch->departments()->count() > 0) {
            return redirect()->route('admin.branches.index')->with('error', __('ui.this_branch_has_departments_and_cannot_be_deleted'));
        }

        $branch->delete();

        return redirect()->route('admin.branches.index')->with('success', __('ui.branch_deleted_successfully'));
    }
}
