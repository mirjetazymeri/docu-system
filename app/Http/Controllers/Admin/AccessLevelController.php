<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccessLevel;
use App\Models\Permission;
use App\Models\Module;
use App\Models\User;



class AccessLevelController extends Controller
{
      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('sentinelAccess:access.view')->only(['index', 'show']);
        $this->middleware('sentinelAccess:access.create')->only(['create', 'store']);
        $this->middleware('sentinelAccess:access.edit')->only(['edit', 'update']);
        $this->middleware('sentinelAccess:access.delete')->only('destroy');
    }





    public function index()
    {

        $accessLevels = AccessLevel::all();
        return view('admin.access-levels.index', compact('accessLevels'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permissions = Permission::all();
        $modules = Module::all();
        return view('admin.access-levels.create', compact('permissions', 'modules'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Create the AccessLevel
        $accessLevel = AccessLevel::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Attach permissions to the AccessLevel
        $accessLevel->permissions()->attach($request->permissions);

        // Redirect back with success message
        return redirect()->route('admin.access-levels.index')
                         ->with('success', __('ui.access_level_created_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $accessLevel = AccessLevel::findOrFail($id);
        return view('admin.access-levels.show', compact('accessLevel'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $accessLevel = AccessLevel::findOrFail($id);
        $permissions = Permission::all();
        $modules = Module::all();
        return view('admin.access-levels.edit', compact('accessLevel', 'permissions', 'modules'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Find the AccessLevel
        $accessLevel = AccessLevel::findOrFail($id);

        // Update the AccessLevel
        $accessLevel->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Sync permissions to the AccessLevel
        $accessLevel->permissions()->sync($request->permissions);

        // Redirect back with success message
        return redirect()->route('admin.access-levels.index')
                         ->with('success', __('ui.access_level_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $accessLevel = AccessLevel::findOrFail($id);
        $accessLevel->delete();

        return redirect()->route('admin.access-levels.index')
                         ->with('success', __('ui.access_level_deleted_successfully'));
    }



    public function createPermission()
    {
        $modules = Module::all();
        return view('admin.permissions.create', compact('modules'));
    }

    /**
     * Store a newly created permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePermission(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string',
            'module_id' => 'required|exists:modules,id',
        ]);

        // Create the Permission
        Permission::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'module_id' => $request->module_id,
        ]);

        // Redirect back with success message
        return redirect()->route('admin.access-levels.index')
                         ->with('success', __('ui.permission_created_successfully'));
    }


}
