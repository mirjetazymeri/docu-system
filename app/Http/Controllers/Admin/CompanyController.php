<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use DataTables;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Company::all();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function($row) {
                    return view('admin.companies.actions', compact('row'))->render();
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.companies.index');
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Company::create($request->all());

        return redirect()->route('admin.companies.index')->with('success', __('ui.company_created_successfully'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $company->update($request->all());

        return redirect()->route('admin.companies.index')->with('success', __('ui.company_updated_successfully'));
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);

        // Check if the company has branches
        if ($company->branches()->count() > 0) {
            // Redirect back with an error message if the company has branches
            return redirect()->route('admin.companies.index')->with('error', __('ui.this_company_has_branches_and_cannot_be_deleted'));
        }

        // Proceed with deletion if there are no branches
        $company->delete();

        // Redirect to the companies list with a success message
        return redirect()->route('admin.companies.index')->with('success', __('ui.company_deleted_successfully'));
    }
}
