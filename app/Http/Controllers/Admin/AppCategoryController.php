<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppCategory;
use Illuminate\Http\Request;

class AppCategoryController extends Controller
{
    public function index()
    {
        $categories = AppCategory::all();
        return view('admin.appcategories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.appcategories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|unique:app_categories|max:255',
        ]);

        AppCategory::create($request->all());

        return redirect()->route('admin.appcategories.index')->with('success', __('ui.category_created_successfully'));
    }

    public function edit($id)
    {
        $appCategory = AppCategory::findOrFail($id);
        return view('admin.appcategories.edit', compact('appCategory'));
    }

    public function update(Request $request, $id)
    {
        $appCategory = AppCategory::findOrFail($id);

        $request->validate([
            'category_name' => 'required|unique:app_categories,category_name,' . $id,
        ]);

        $appCategory->update(['category_name' => $request->category_name]);

        return redirect()->route('admin.appcategories.index')->with('success', __('ui.category_updated_successfully'));
    }

    public function destroy($id)
    {
        $appCategory = AppCategory::findOrFail($id);
        $appCategory->delete();
        return redirect()->route('admin.appcategories.index')->with('success', __('ui.category_deleted_successfully'));
    }
}
