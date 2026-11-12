<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        Setting::updateOrCreate(['key' => 'app_name'], ['value' => $request->app_name]);

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $filename = 'app_logo.' . $logo->getClientOriginalExtension();

            $oldLogo = Setting::where('key', 'logo')->first();
            if ($oldLogo && Storage::disk('public')->exists($oldLogo->value)) {
                Storage::disk('public')->delete($oldLogo->value);
            }

            $path = $logo->storeAs('logos', $filename, 'public');

            if ($path) {
                Setting::updateOrCreate(['key' => 'logo'], ['value' => $path]);
            } else {
                return redirect()->route('admin.settings.index')->with('error', __('ui.failed_to_upload_logo'));
            }
        }

        return redirect()->route('admin.settings.index')->with('success', __('ui.settings_updated_successfully'));
    }
}
