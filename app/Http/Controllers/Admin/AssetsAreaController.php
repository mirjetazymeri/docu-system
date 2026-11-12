<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetsArea;
use App\Models\DeviceType;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Workplace;
use Illuminate\Http\Request;
use PDF;

class AssetsAreaController extends Controller
{public function index()
    {
        $assets = AssetsArea::with(['deviceType', 'brand', 'company', 'branch', 'department', 'workplace'])->get();
        return view('admin.assets_area.index', compact('assets'));
    }

    public function create()
    {
        $companies = Company::all();
        $branches = Branch::all();
        $departments = Department::all();
        $workplaces = Workplace::all();
        $deviceTypes = DeviceType::all();
        $brands = Brand::all();

        return view('admin.assets_area.create', compact('companies', 'branches', 'departments', 'workplaces', 'deviceTypes', 'brands'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'device_type_id' => 'required|exists:device_types,id',
            'brand_id' => 'required|exists:brands,id',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'workplace_id' => 'required|exists:workplaces,id',
            'serial_number' => 'required|string|max:255',
            'cpu' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'storage' => 'nullable|string|max:255',
            'gpu' => 'nullable|string|max:255',
        ]);

        AssetsArea::create($validated);

        return redirect()->route('admin.assets-areas.index')->with('success', __('ui.asset_created_successfully'));
    }

    public function edit($id)
    {
        $asset = AssetsArea::findOrFail($id);
        $deviceTypes = DeviceType::all();
        $brands = Brand::all();
        return view('admin.assets_area.edit', compact('asset', 'deviceTypes', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'device_type_id' => 'required|exists:device_types,id',
            'brand_id' => 'required|exists:brands,id',
            'serial_number' => 'required|string|max:255',
            'cpu' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'storage' => 'nullable|string|max:255',
            'gpu' => 'nullable|string|max:255',
        ]);

        $asset = AssetsArea::findOrFail($id);
        $asset->update($validated);

        return redirect()->route('admin.assets-areas.index')->with('success', __('ui.asset_area_updated_successfully'));
    }
    public function destroy($id)
    {
        $asset = AssetsArea::findOrFail($id);
        $asset->delete();
        return redirect()->route('admin.assets-areas.index')->with('success', __('ui.asset_area_deleted_successfully'));
    }


    public function generatePdf($id)
    {
        $asset = AssetsArea::find($id);  // Fetch the asset based on the ID

        // Check if the asset exists
        if (!$asset) {
            return redirect()->route('admin.assets-areas.index')->with('error', __('ui.asset_not_found'));
        }

        // Load the view from the correct path, passing a single asset
        $pdf = PDF::loadView('admin.assets_area.pdf', compact('asset'));

        // Stream the PDF to the browser
        return $pdf->stream('asset-' . $asset->id . '-report.pdf');
    }


}
