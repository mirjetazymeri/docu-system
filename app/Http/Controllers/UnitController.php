<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesApplicationAccess;
use App\Models\Unit;
use App\Models\Application;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    use AuthorizesApplicationAccess;


    public function index(Application $application)
    {
        $this->authorizeApplicationPermission($application, 'unit.view');
        // Fetch the units associated with the application
        $units = $application->units; // This should be a collection
        $canCreateUnits = $this->canEditApplication(
            $application,
            'unit.create'
        );
        $canEditUnits = $this->canEditApplication(
            $application,
            'unit.edit'
        );
        $canImportTestDocu = (int) $application->id === 50
            && strcasecmp(trim($application->name), 'TestDocu') === 0
            && $canCreateUnits
            && $this->canEditApplication($application, 'documentation.create');

        return view(
            'admin.units.index',
            compact(
                'units',
                'application',
                'canCreateUnits',
                'canImportTestDocu',
                'canEditUnits'
            )
        );
    }

    public function create(Application $application)
    {
        $this->authorizeApplicationEdit($application, 'unit.create');
        // Return the view for creating a unit
        return view('admin.units.create', compact('application'));
    }

    public function store(Request $request, Application $application)
    {
        $this->authorizeApplicationEdit($application, 'unit.create');
        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255', // Validation rules
        ]);

        // Create the new unit associated with the application
        $unit = new Unit();
        $unit->name = $validated['name'];
        $unit->application_id = $application->id; // Assign the foreign key
        $unit->save();

        return redirect()->route('units.index', ['application' => $application->id])
                         ->with('success', __('ui.unit_created_successfully'));
    }

    public function edit(Application $application, Unit $unit)
    {
        $this->authorizeApplicationEdit($application, 'unit.edit');
        abort_unless($unit->application_id === $application->id, 404);
        // Return the view for editing the unit
        return view('admin.units.edit', compact('application', 'unit'));
    }

    public function update(Request $request, Application $application, Unit $unit)
    {
        $this->authorizeApplicationEdit($application, 'unit.edit');
        abort_unless($unit->application_id === $application->id, 404);
        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255', // Validation rules
        ]);

        // Update the unit's name
        $unit->name = $validated['name'];
        $unit->save();

        return redirect()->route('units.index', ['application' => $application->id])
                         ->with('success', __('ui.unit_updated_successfully'));
    }

    public function destroy(Application $application, Unit $unit)
    {
        $this->authorizeApplicationAdministrator();
        abort_unless($unit->application_id === $application->id, 404);
        // Delete the unit
        $unit->delete();

        // Redirect back to the units index with a success message
        return redirect()->route('units.index', ['application' => $application->id])
                         ->with('success', __('ui.unit_deleted_successfully'));
    }
}
