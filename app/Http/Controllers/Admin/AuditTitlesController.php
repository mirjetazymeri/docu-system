<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTitle;
use Illuminate\Http\Request;

class AuditTitlesController extends Controller
{


    public function index()
    {
        $titles = AuditTitle::all();
        return view('admin.titles.index', compact('titles'));
    }


    public function create()
    {
        return view('admin.titles.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        AuditTitle::create($request->all());

        return redirect()->route('admin.audit-titles.index')->with('success', __('ui.title_created_successfully'));
    }

    public function show(AuditTitle $auditTitle)
    {
        return view('admin.titles.show', compact('auditTitle'));
    }


    public function edit($id)
    {
        $auditTitle = AuditTitle::findOrFail($id);
        return view('admin.titles.edit', compact('auditTitle'));
    }

    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $auditTitle = AuditTitle::findOrFail($id);

        $auditTitle->update($validated);

        return redirect()->route('admin.audit-titles.index')
            ->with('success', __('ui.audit_title_updated_successfully'));
    }



    public function destroy($id)
    {
        $auditTitle = AuditTitle::findOrFail($id);
        $auditTitle->delete();

        return redirect()->route('admin.audit-titles.index')->with('success', __('ui.title_deleted_successfully'));
    }
}
