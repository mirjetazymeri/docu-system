<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Audit;
use App\Models\Company;
use App\Models\Branch;
use App\Models\AuditTitle;
use PDF;
use DataTables;

class AuditController extends Controller
{

    // public function index()
    // {
    //     $audits = Audit::with(['branch', 'company'])->get(); // Use eager loading for relationships
    //     return view('admin.audits.index', compact('audits'));
    // }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Audit::with(['branch', 'company', 'auditTitle'])->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('auditTitleName', function($row) {
                    return $row->auditTitle ? $row->auditTitle->name : 'N/A'; // Safely access name
                })
                ->addColumn('actions', function($row) {
                    return view('admin.audits.actions', compact('row'))->render();
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.audits.index');
    }


    public function create()
    {
        $companies = Company::all();
        $branches = Branch::all();
        $auditTitles = AuditTitle::all(); // Fetch all audit titles

        return view('admin.audits.create', compact('companies', 'branches', 'auditTitles'));
    }
    public function show($id)
    {
        $audit = Audit::with(['company', 'branch'])->findOrFail($id);
        return view('admin.audits.show', compact('audit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'audit_title_id' => 'required|exists:audit_titles,id', // Ensure audit_title_id is being passed correctly

            'created_date' => 'nullable|date',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'content' => 'required|string',
        ]);



        // If validation passes, create the audit
        Audit::create($validated);

        return redirect()->route('admin.audits.index')->with('success', __('ui.audit_created_successfully'));
    }


    public function edit($id)
    {
        $audit = Audit::findOrFail($id);
        $companies = Company::all();
        $branches = Branch::all();
        return view('admin.audits.edit', compact('audit', 'companies', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'audit_title_id' => 'nullable|string|max:255',
            'created_date' => 'nullable|date',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id', // Make branch optional
            'content' => 'required|string',
        ]);

        $audit = Audit::findOrFail($id);
        $audit->update($validated);

        return redirect()->route('admin.audits.index')->with('success', __('ui.audit_updated_successfully'));
    }

    public function destroy($id)
    {
        $audit = Audit::findOrFail($id);
        $audit->delete();

        return redirect()->route('admin.audits.index')->with('success', __('ui.audit_deleted_successfully'));
    }

    public function generatePdf($id)
    {
        $audit = Audit::with(['company', 'branch'])->findOrFail($id);

        // Pass audit data to a specific Blade view for PDF
        $pdf = PDF::loadView('admin.audits.pdf', compact('audit'));

        // Set the filename and download options
        return $pdf->stream("audit_report_{$audit->id}.pdf");
    }
}
