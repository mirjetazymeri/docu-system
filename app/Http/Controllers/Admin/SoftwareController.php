<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SoftwareCompany;
use App\Models\SoftwareCompanyDetail;
use App\Models\Application;
use Illuminate\Http\Request;
use PDF;
class SoftwareController extends Controller
{
    public function index()
{
    // Fetch software test data with the related software company and application
    $softwareDetails = SoftwareCompanyDetail::with('softwareCompany.application')->get();
    return view('admin.software.index', compact('softwareDetails'));
}

    public function create()
    {
        // Get all applications for the dropdown list
        $applications = Application::all();
        return view('admin.software.create', compact('applications'));
    }
    public function store(Request $request)
    {
        // Validate the form data
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'tests' => 'required|array', // Array of test data
            'tests.*.category' => 'nullable|string', // Add category validation
            'tests.*.module' => 'required|string',
            'tests.*.feature' => 'required|string',
            'tests.*.result' => 'required|in:passed,not_passed,in_process',
            'tests.*.comment' => 'nullable|string',
        ]);

        // Fetch the application to get the name
        $application = Application::findOrFail($validated['application_id']);

        // Loop through each test and store data
        foreach ($validated['tests'] as $test) {
            // Create the software company (one per unique application)
            $softwareCompany = SoftwareCompany::firstOrCreate([
                'application_id' => $validated['application_id'],
                'name' => $application->name, // Use the actual application name
            ]);

            // Create the software company details
            SoftwareCompanyDetail::create([
                'software_company_id' => $softwareCompany->id,
                'category' => $test['category'] ?? null,
                'module' => $test['module'],
                'feature' => $test['feature'],
                'result' => $test['result'],
                'comment' => $test['comment'],
            ]);
        }

        // Redirect to the software list with success message
        return redirect()->route('admin.software.index')->with('success', __('ui.software_test_data_added_successfully'));
    }


    public function edit($id)
    {
        // Fetch the software test details by ID
        $softwareTest = SoftwareCompanyDetail::with('softwareCompany.application')->findOrFail($id);
        // Get all applications for the dropdown list
        $applications = Application::all();
        return view('admin.software.edit', compact('softwareTest', 'applications'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'category' => 'nullable|string',
            'module' => 'required|string|max:255',
            'feature' => 'required|string|max:255',
            'result' => 'required|in:passed,not_passed,in_process',
            'comment' => 'nullable|string',
        ]);

        // Find and update the software test data
        $softwareTest = SoftwareCompanyDetail::findOrFail($id);
        $softwareTest->update([
            'category' => $request->category,
            'module' => $request->module,
            'feature' => $request->feature,
            'result' => $request->result,
            'comment' => $request->comment,
        ]);

        return redirect()->route('admin.software.index')->with('success', __('ui.test_data_updated_successfully'));
    }

    public function destroy($id)
    {
        // Delete the software test data
        $softwareTest = SoftwareCompanyDetail::findOrFail($id);
        $softwareTest->delete();

        return redirect()->route('admin.software.index')->with('success', __('ui.test_data_deleted_successfully'));
    }
    public function generatePdf()
    {
        // Fetch data to be included in the PDF (e.g., SoftwareCompanyDetails with relations)
        $softwareDetails = SoftwareCompanyDetail::with('softwareCompany.application')->get();

        // Pass the data to the view and generate the PDF
        $pdf = PDF::loadView('admin.software.pdf', compact('softwareDetails'));

        // Return the PDF for download
        return $pdf->download('software_test_report.pdf');
    }

}
