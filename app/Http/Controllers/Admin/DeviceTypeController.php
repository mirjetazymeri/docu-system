<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceType;
use Illuminate\Http\Request;

class DeviceTypeController extends Controller
{
    public function index()
    {

        $deviceTypes = DeviceType::all();
        return view('admin.device_type.index', compact('deviceTypes'));
    }

    public function create()
    {

        return view('admin.device_type.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DeviceType::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.device-types.index')->with('success', __('ui.device_type_created_successfully'));
    }

    public function edit($id)
    {

        $deviceType = DeviceType::findOrFail($id);
        return view('admin.device_type.edit', compact('deviceType'));
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $deviceType = DeviceType::findOrFail($id);
        $deviceType->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.device-types.index')->with('success', __('ui.device_type_updated_successfully'));
    }

    public function show($id)
    {

        $deviceType = DeviceType::findOrFail($id);
        return view('admin.device_type.show', compact('deviceType'));
    }

    public function destroy($id)
    {

        $deviceType = DeviceType::findOrFail($id);
        $deviceType->delete();

        return redirect()->route('admin.device-types.index')->with('success', __('ui.device_type_deleted_successfully'));
    }
}
