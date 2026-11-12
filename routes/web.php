<?php


require_once 'web_builder.php';

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmployeeLeaveController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TestingIssueController;
use App\Http\Controllers\Admin\AuditTitlesController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\DeviceTypeController;
use App\Http\Controllers\Admin\AssetsAreaController;
use App\Http\Controllers\Admin\AppTestingController;
use App\Http\Controllers\Admin\SoftwareController;
use App\Http\Controllers\Admin\ApplicationTestingController;
use App\Http\Controllers\Admin\AppCategoryController;

/*

|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!

|
*/


// Application Management Routes
Route::post('/applications/{application}/import-TestDocu-documentation', \App\Http\Controllers\TestDocuDocumentationImportController::class)
    ->name('applications.import-TestDocu-documentation');

Route::group(['prefix' => 'applications', 'as' => 'applications.'], function () {
    Route::get('/', [ApplicationController::class, 'index'])->name('index');
    Route::get('/status', [ApplicationController::class, 'status'])->name('status');
    Route::get('/create', [ApplicationController::class, 'create'])->name('create');
    Route::post('/', [ApplicationController::class, 'store'])->name('store');
    Route::get('/{application}/edit', [ApplicationController::class, 'edit'])->name('edit');
    Route::delete('/{application}/destroy', [ApplicationController::class, 'destroy'])->name('destroy'); // Changed to DELETE
    Route::put('/{application}/update', [ApplicationController::class, 'update'])->name('update');
    Route::patch('/{application}/documentation-status', [ApplicationController::class, 'updateDocumentationStatus'])
        ->name('documentation-status');
});

Route::group(['prefix' => 'applications/{application}/units', 'as' => 'units.'], function () {
    Route::get('/', [UnitController::class, 'index'])->name('index');
    Route::get('/create', [UnitController::class, 'create'])->name('create');
    Route::post('/', [UnitController::class, 'store'])->name('store');
    Route::get('/{unit}/edit', [UnitController::class, 'edit'])->name('edit');
    Route::put('/{unit}', [UnitController::class, 'update'])->name('update');
    Route::delete('/{unit}', [UnitController::class, 'destroy'])->name('destroy');
});






Route::prefix('applications/{application}/manuals')->group(function () {
    Route::get('/', [ManualController::class, 'index'])->name('manuals.index');
    Route::get('create', [ManualController::class, 'create'])->name('manuals.create');
    Route::post('/', [ManualController::class, 'store'])->name('manuals.store');
    Route::get('{manual}/edit', [ManualController::class, 'edit'])->name('manuals.edit');
    Route::put('{manual}', [ManualController::class, 'update'])->name('manuals.update');
    Route::delete('{manual}', [ManualController::class, 'destroy'])->name('manuals.destroy');
    Route::get('{manual}/pdf', [ManualController::class, 'downloadPDF'])->name('manuals.pdf');
});

// Route to create a new Unit for a specific Application
Route::get('/applications/{application}/units/create', [UnitController::class, 'create'])->name('units.create');
// Route to show the form for creating a new Documentation
Route::get('/applications/{application}/units/{unit}/documentation/create', [DocumentationController::class, 'create'])->name('documentation.create');
// Route to store documentation for a specific Unit
Route::post('/applications/{application}/units/{unit}/documentations', [DocumentationController::class, 'store'])->name('units.documentation.store');


// Route to list all documentations for a specific Unit under an Application
Route::get('/applications/{application}/units/{unit}/documentations', [DocumentationController::class, 'index'])->name('units.documentation.index');

// Route to show a specific Documentation for a specific Unit
Route::get('/applications/{application}/units/{unit}/documentation/{documentation}', [DocumentationController::class, 'show'])->name('documentation.show');

// Route to show the form for editing a specific Documentation
Route::get('/applications/{application}/units/{unit}/documentation/{documentation}/edit', [DocumentationController::class, 'edit'])->name('documentation.edit');

// Route to update a specific Documentation for a specific Unit
Route::put('/applications/{application}/units/{unit}/documentation/{documentation}', [DocumentationController::class, 'update'])->name('documentation.update');

// Route to store documentation for a specific Unit
Route::post('/applications/{application}/units/{unit}/documentations', [DocumentationController::class, 'store'])->name('units.documentation.store');

// Route to delete a specific Documentation for a specific Unit
Route::delete('/applications/{application}/units/{unit}/documentation/{documentation}', [DocumentationController::class, 'destroy'])->name('documentation.destroy');

// General route to list all documentations (if needed)
Route::get('/documentation', [DocumentationController::class, 'index'])->name('admin.documentation.index');


Route::get('/documentations/{documentation}/pdf', [DocumentationController::class, 'generatePdf'])->name('documentations.generatePdf');
Route::get('/applications/{application}/units/{unit}/documentations/{documentation}/pdf', [DocumentationController::class, 'generatePdf'])->name('documentations.generatePdf');
Route::get('/applications/{application}/pdf', [DocumentationController::class, 'generatePdfAll'])->name('documentations.generatePdfAll');
Route::get('/applications/{application}/pdf/parts/{unitId}/{documentId}', [DocumentationController::class, 'generateApplicationPdfPart'])
    ->whereNumber('unitId')->whereNumber('documentId')->name('documentations.applicationPdfPart');

Route::middleware('user')->prefix('testing-issues')->name('testing-issues.')->group(function () {
    Route::get('/', [TestingIssueController::class, 'index'])->name('index');
    Route::post('/{testingIssue}/submit-fix', [TestingIssueController::class, 'submitFix'])->name('submit-fix');
    Route::patch('/{testingIssue}/verify', [TestingIssueController::class, 'verify'])->name('verify');
    Route::patch('/{testingIssue}/reopen', [TestingIssueController::class, 'reopen'])->name('reopen');
});





Route::pattern('slug', '[a-z0-9- _]+');




Route::group(
    ['prefix' => 'admin', 'namespace' => 'Admin'],
    function () {

        // Error pages should be shown without requiring login
        Route::get(
            '404',
            function () {
                return view('admin.404');
            }
        );
        Route::get(
            '500',
            function () {
                return view('admin.500');
            }
        );


        // Lock screen
        Route::get('{id}/lockscreen', 'LockscreenController@show')->name('lockscreen');
        Route::post('{id}/lockscreen', 'LockscreenController@check')->name('lockscreen');
        // All basic routes defined here
        Route::get('login', 'AuthController@getSignin')->name('login');
        Route::get('signin', 'AuthController@getSignin')->name('signin');
        Route::post('signin', 'AuthController@postSignin')->name('postSignin');
        Route::post('signup', 'AuthController@postSignup')->name('admin.signup');
        Route::post('forgot-password', 'AuthController@postForgotPassword')->name('forgot-password');
        Route::get(
            'login2',
            function () {
                return view('admin.login2');
            }
        );

        // Register2
        Route::get(
            'register2',
            function () {
                return view('admin.register2');
            }
        );
        Route::post('register2', 'AuthController@postRegister2')->name('register2');

        // Forgot Password Confirmation
        //    Route::get('forgot-password/{userId}/{passwordResetCode}', 'AuthController@getForgotPasswordConfirm')->name('forgot-password-confirm');
        //    Route::post('forgot-password/{userId}/{passwordResetCode}', 'AuthController@getForgotPasswordConfirm');

        // Logout
        Route::get('logout', 'AuthController@getLogout')->name('admin.logout');

        // Account Activation
        Route::get('activate/{userId}/{activationCode}', 'AuthController@getActivate')->name('activate');
    }
);

Route::group(
    ['prefix' => 'admin', 'middleware' => 'admin', 'as' => 'admin.'],
    function () {
        // GUI Crud Generator
        Route::get('generator_builder', 'JoshController@builder')->name('generator_builder');
        Route::get('field_template', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@fieldTemplate');
        Route::post('generator_builder/generate', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@generate');
        // Model checking
        Route::post('modelCheck', 'ModelcheckController@modelCheck');

        // Dashboard / Index
        // Route::get(
        //     '/',
        //     function () {
        //         return view('admin.index');
        //     }
        // )->name('dashboard');

        Route::get('/', 'Admin\DashboardController@index')->name('dashboard');
        Route::get(
            'documentation-workers',
            'Admin\DocumentationWorkerController@index'
        )->name('documentation-workers.index');
        Route::get(
            'documentation-workers/{user}',
            'Admin\DocumentationWorkerController@show'
        )->name('documentation-workers.show');


        Route::get('index1', 'JoshController@showHome');
        // crop demo
        Route::post('crop_demo', 'JoshController@cropDemo')->name('crop_demo');
        //Log viewer routes
        Route::get('log_viewers', 'Admin\LogViewerController@index')->name('log-viewers');
        Route::get('log_viewers/logs', 'Admin\LogViewerController@listLogs')->name('log_viewers.logs');
        Route::delete('log_viewers/logs/delete', 'Admin\LogViewerController@delete')->name('log_viewers.logs.delete');
        Route::get('log_viewers/logs/{date}', 'Admin\LogViewerController@show')->name('log_viewers.logs.show');
        Route::get('log_viewers/logs/{date}/download', 'Admin\LogViewerController@download')->name('log_viewers.logs.download');
        Route::get('log_viewers/logs/{date}/{level}', 'Admin\LogViewerController@showByLevel')->name('log_viewers.logs.filter');
        Route::get('log_viewers/logs/{date}/{level}/search', 'Admin\LogViewerController@search')->name('log_viewers.logs.search');
        Route::get('log_viewers/logcheck', 'Admin\LogViewerController@logCheck')->name('log-viewers.logcheck');
        //end Log viewer
        // Activity log
        Route::get('activity_log/data', 'JoshController@activityLogData')->name('activity_log.data');
        //    Route::get('/', 'JoshController@index')->name('index');

    

 // Application Management Routes
 Route::group(['prefix' => 'applications', 'as' => 'applications.'], function () {
    Route::get('/', [ApplicationController::class, 'index'])->name('index');
    Route::get('/create', [ApplicationController::class, 'create'])->name('create');
    Route::post('/', [ApplicationController::class, 'store'])->name('store');
});



        Route::resource('companies', Admin\CompanyController::class);
        Route::resource('branches', Admin\BranchController::class);
        Route::resource('departments', Admin\DepartmentController::class);
        Route::resource('workplaces', Admin\WorkplaceController::class);

        Route::resource('access-levels', Admin\AccessLevelController::class);

        Route::get('modules/create', 'Admin\AccessLevelController@createModule')->name('modules.create');
        Route::post('modules', 'Admin\AccessLevelController@storeModule')->name('modules.store');

        Route::get('permissions/create', 'Admin\AccessLevelController@createPermission')->name('permissions.create');
        Route::post('permissions', 'Admin\AccessLevelController@storePermission')->name('permissions.store');


        Route::resource('employees', Admin\EmployeeController::class);
        Route::post('employees/storeUser', 'Admin\EmployeeController@storeUser')->name('employees.storeUser');
        Route::get('employees/{employee}/report', 'Admin\EmployeeController@generateEmployeeReport')->name('employees.report');




        Route::resource('contracts', Admin\ContractController::class);
        Route::post('contracts/{contract}/close', 'Admin\ContractController@close')->name('contracts.close');
        Route::get('contracts/{contract}/pdf', 'Admin\ContractController@generatePdf')->name('contracts.pdf');

        Route::resource('leave_types', Admin\LeaveTypeController::class);
        // Route::resource('employee_reviews', Admin\EmployeeReviewController::class);
        Route::get('employee_reviews', 'Admin\EmployeeReviewController@index')->name('employee_reviews.index');
        Route::get('employee_reviews/create', 'Admin\EmployeeReviewController@create')->name('employee_reviews.create');
        Route::post('employee_reviews', 'Admin\EmployeeReviewController@store')->name('employee_reviews.store');
        Route::get('employee_reviews/{employee_review}', 'Admin\EmployeeReviewController@show')->name('employee_reviews.show');
        Route::get('employee_reviews/{employee_review}/edit', 'Admin\EmployeeReviewController@edit')->name('employee_reviews.edit');
        Route::put('employee_reviews/{employee_review}', 'Admin\EmployeeReviewController@update')->name('employee_reviews.update');
        Route::delete('employee_reviews/{employee_review}', 'Admin\EmployeeReviewController@destroy')->name('employee_reviews.destroy');





        Route::resource('subjects', Admin\SubjectController::class);

        //  route for get suppliers on expense controller
        Route::get('get_suppliers', 'Admin\ExpenseController@get_suppliers')->name('get_suppliers');

        //search methods
        Route::get('get_users', 'Admin\SearchController@get_users')->name('get_users');
        Route::get('get_employees', 'Admin\SearchController@get_employees')->name('get_employees');


        Route::get('reports', 'Admin\ReportsController@index')->name('admin.reports.index');
        Route::get('reports/vehicles', 'Admin\ReportsController@vehiclesReport')->name('reports.vehicles');
        Route::get('reports/employees', 'Admin\ReportsController@employeesReport')->name('reports.employees');

        //Settings
        Route::get('settings', 'Admin\SettingsController@index')->name('settings.index');
        Route::put('settings', 'Admin\SettingsController@update')->name('settings.update');



        Route::resource('item-types', Admin\ItemTypeController::class);
        Route::resource('items', Admin\ItemController::class);
        Route::resource('account_types', Admin\AccountTypeController::class);
        Route::resource('accounts', Admin\AccountController::class);
        // Route::resource('branchstocks', Admin\BranchStockController::class);



        Route::resource('purchases', Admin\PurchaseController::class);
        Route::get('/purchases/{id}/items', 'Admin\PurchaseController@getPurchaseItems')->name('purchases.items');

        Route::post('/purchases/calculate', 'Admin\PurchaseController@calculate')->name('purchases.calculate');




        Route::get('/translations', 'Admin\TranslationController@index')->name('translations.index');
        Route::get('/translations/create', 'Admin\TranslationController@create')->name('translations.create');
        Route::post('/translations', 'Admin\TranslationController@store')->name('translations.store');
        Route::get('/translations/{key}/edit', 'Admin\TranslationController@edit')->name('translations.edit');
        Route::put('/translations/{key}', 'Admin\TranslationController@update')->name('translations.update');



        Route::resource('application_testing', ApplicationTestingController::class);
        Route::get('/application_testing/getUnits/{applicationId}', [ApplicationTestingController::class, 'getUnits'])->name('application_testing.getUnits');
        Route::get('application-testing/{application_testing}/generate-pdf', [ApplicationTestingController::class, 'generatePdf'])->name('application_testing.generatePdf');
        Route::post('application-testing/{application_testing}/send-report', [ApplicationTestingController::class, 'sendReport'])->name('application_testing.sendReport');
        Route::patch('application-testing/{application_testing}/completion', [ApplicationTestingController::class, 'updateCompletion'])->name('application_testing.completion');






        // Index route for audits
        Route::get('audits', [AuditController::class, 'index'])->name('audits.index');

        // Create route for audits
        Route::get('audits/create', [AuditController::class, 'create'])->name('audits.create');

        // Store route for audits
        Route::post('audits', [AuditController::class, 'store'])->name('audits.store');

        // Edit route for audits
        Route::get('audits/{id}/edit', [AuditController::class, 'edit'])->name('audits.edit');

        // Update route for audits
        Route::put('audits/{id}', [AuditController::class, 'update'])->name('audits.update');

        // Destroy route for audits
        Route::delete('audits/{id}', [AuditController::class, 'destroy'])->name('audits.destroy');

        // Show route for audits
        Route::get('audits/{id}', [AuditController::class, 'show'])->name('audits.show');

        Route::get('admin/audits/{id}/pdf', [AuditController::class, 'generatePdf'])->name('admin.audits.pdf');

        Route::get('{audits}/pdf', [AuditController::class, 'generatePdf'])->name('audits.pdf');



        // Index route for audit titles
        Route::get('audit-titles', [AuditTitlesController::class, 'index'])->name('audit-titles.index');

        // Create route for audit titles
        Route::get('audit-titles/create', [AuditTitlesController::class, 'create'])->name('audit-titles.create');

        // Store route for audit titles
        Route::post('audit-titles', [AuditTitlesController::class, 'store'])->name('audit-titles.store');

        // Edit route for audit titles
        Route::get('audit-titles/{id}/edit', [AuditTitlesController::class, 'edit'])->name('audit-titles.edit');

        // Update route for audit titles
        Route::put('audit-titles/{id}', [AuditTitlesController::class, 'update'])->name('audit-titles.update');

        // Show route for audit titles
        Route::get('audit-titles/{id}', [AuditTitlesController::class, 'show'])->name('audit-titles.show');

        // Delete route for audit titles
        Route::delete('audit-titles/{id}', [AuditTitlesController::class, 'destroy'])->name('audit-titles.destroy');






// Index route for app categories
Route::get('admin/appcategories', [AppCategoryController::class, 'index'])->name('appcategories.index');

// Create route for app categories
Route::get('admin/appcategories/create', [AppCategoryController::class, 'create'])->name('appcategories.create');

// Store route for app categories
Route::post('admin/appcategories', [AppCategoryController::class, 'store'])->name('appcategories.store');

// Edit route for app categories
Route::get('admin/appcategories/{id}/edit', [AppCategoryController::class, 'edit'])->name('appcategories.edit');

// Update route for app categories
Route::put('admin/appcategories/{id}', [AppCategoryController::class, 'update'])->name('appcategories.update');

// Destroy route for app categories
Route::delete('admin/appcategories/{id}', [AppCategoryController::class, 'destroy'])->name('appcategories.destroy');

// Show route for app categories
Route::get('admin/appcategories/{id}', [AppCategoryController::class, 'show'])->name('appcategories.show');







































        Route::get('brands', [BrandController::class, 'index'])->name('brands.index');

        // Create route for brands
        Route::get('brands/create', [BrandController::class, 'create'])->name('brands.create');

        // Store route for brands
        Route::post('brands', [BrandController::class, 'store'])->name('brands.store');

        // Edit route for brands
        Route::get('brands/{id}/edit', [BrandController::class, 'edit'])->name('brands.edit');

        // Update route for brands
        Route::put('brands/{id}', [BrandController::class, 'update'])->name('brands.update');

        // Show route for brands
        Route::get('brands/{id}', [BrandController::class, 'show'])->name('brands.show');

        // Delete route for brands
        Route::delete('brands/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');





        Route::get('device-types', [DeviceTypeController::class, 'index'])->name('device-types.index');

        // Create route for device types
        Route::get('device-types/create', [DeviceTypeController::class, 'create'])->name('device-types.create');

        // Store route for device types
        Route::post('device-types', [DeviceTypeController::class, 'store'])->name('device-types.store');

        // Edit route for device types
        Route::get('device-types/{id}/edit', [DeviceTypeController::class, 'edit'])->name('device-types.edit');

        // Update route for device types
        Route::put('device-types/{id}', [DeviceTypeController::class, 'update'])->name('device-types.update');

        // Show route for device types
        Route::get('device-types/{id}', [DeviceTypeController::class, 'show'])->name('device-types.show');

        // Delete route for device types
        Route::delete('device-types/{id}', [DeviceTypeController::class, 'destroy'])->name('device-types.destroy');







// Index route for assets areas
Route::get('assets-areas', [AssetsAreaController::class, 'index'])->name('assets-areas.index');

// Create route for assets areas
Route::get('assets-areas/create', [AssetsAreaController::class, 'create'])->name('assets-areas.create');

// Store route for assets areas
Route::post('assets-areas', [AssetsAreaController::class, 'store'])->name('assets-areas.store');

// Edit route for assets areas
Route::get('assets-areas/{id}/edit', [AssetsAreaController::class, 'edit'])->name('assets-areas.edit');

// Update route for assets areas
Route::put('assets-areas/{id}', [AssetsAreaController::class, 'update'])->name('assets-areas.update');

// Show route for assets areas
Route::get('assets-areas/{id}', [AssetsAreaController::class, 'show'])->name('assets-areas.show');

// Delete route for assets areas
Route::delete('assets-areas/{id}', [AssetsAreaController::class, 'destroy'])->name('assets-areas.destroy');

Route::get('assets-areas/{id}/pdf', [AssetsAreaController::class, 'generatePdf'])->name('assets-areas.pdf');






// Index route for app testing
Route::get('app-testing', [AppTestingController::class, 'index'])->name('app-testing.index');

// Create route for app testing
Route::get('app-testing/create', [AppTestingController::class, 'create'])->name('app-testing.create');

// Store route for app testing
Route::post('app-testing', [AppTestingController::class, 'store'])->name('app-testing.store');

// Edit route for app testing
Route::get('app-testing/{id}/edit', [AppTestingController::class, 'edit'])->name('app-testing.edit');

// Update route for app testing
Route::put('app-testing/{id}', [AppTestingController::class, 'update'])->name('app-testing.update');

// Show route for app testing
Route::get('app-testing/{id}', [AppTestingController::class, 'show'])->name('app-testing.show');

// Delete route for app testing
Route::delete('app-testing/{id}', [AppTestingController::class, 'destroy'])->name('app-testing.destroy');





Route::get('software', [SoftwareController::class, 'index'])->name('software.index');

// Create route for software testing
Route::get('software/create', [SoftwareController::class, 'create'])->name('software.create');

// Store route for software testing
Route::post('software', [SoftwareController::class, 'store'])->name('software.store');
Route::post('software', [SoftwareController::class, 'store'])->name('software.store');

// Edit route for software testing
Route::get('software/{id}/edit', [SoftwareController::class, 'edit'])->name('software.edit');

// Update route for software testing
Route::put('software/{id}', [SoftwareController::class, 'update'])->name('software.update');

// Show route for software testing
Route::get('software/{id}', [SoftwareController::class, 'show'])->name('software.show');

// Delete route for software testing
Route::delete('software/{id}', [SoftwareController::class, 'destroy'])->name('software.destroy');

Route::get('software/create', [SoftwareController::class, 'create'])->name('software.create');

// Store route
Route::post('software', [SoftwareController::class, 'store'])->name('software.store');


Route::get('software/create', [SoftwareController::class, 'create'])->name('software.create');

Route::post('software', [SoftwareController::class, 'store'])->name('software.store');


Route::get('admin/software/pdf', [SoftwareController::class, 'generatePdf'])->name('software.generatePdf');


    }
);

Route::group(
    ['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => 'admin', 'as' => 'admin.'],
    function () {

        // User Management
        Route::group(
            ['prefix' => 'users'],
            function () {
                Route::get('data', 'UsersController@data')->name('users.data');
                Route::get('{user}/delete', 'UsersController@destroy')->name('users.delete');
                Route::get('{user}/confirm-delete', 'UsersController@getModalDelete')->name('users.confirm-delete');
                Route::get('{user}/restore', 'UsersController@getRestore')->name('restore.user');
                //        Route::post('{user}/passwordreset', 'UsersController@passwordreset')->name('passwordreset');
                Route::post('passwordreset', 'UsersController@passwordreset')->name('passwordreset');
            }
        );
        Route::resource('users', 'UsersController');
        /*
     * bulk import
    */
        Route::get('bulk_import_users', 'UsersController@import');
        Route::post('bulk_import_users', 'UsersController@importInsert');
        /*
     bulk download
    */
        Route::get('download_users/{type}', 'UsersController@downloadExcel');

        Route::get('deleted_users', ['before' => 'Sentinel', 'uses' => 'UsersController@getDeletedUsers'])->name('deleted_users');

        // Email System
        Route::group(
            ['prefix' => 'emails'],
            function () {
                Route::get('compose', 'EmailController@create');
                Route::post('compose', 'EmailController@store');
                Route::get('inbox', 'EmailController@inbox');
                Route::get('sent', 'EmailController@sent');
                Route::get('{email}', ['as' => 'emails.show', 'uses' => 'EmailController@show']);
                Route::get('{email}/reply', ['as' => 'emails.reply', 'uses' => 'EmailController@reply']);
                Route::get('{email}/forward', ['as' => 'emails.forward', 'uses' => 'EmailController@forward']);
            }
        );
        Route::resource('emails', 'EmailController');

        // Role Management
        Route::group(
            ['prefix' => 'roles'],
            function () {
                Route::get('{group}/delete', 'RolesController@destroy')->name('roles.delete');
                Route::get('{group}/confirm-delete', 'RolesController@getModalDelete')->name('roles.confirm-delete');
                Route::get('{group}/restore', 'RolesController@getRestore')->name('roles.restore');
            }
        );
        Route::resource('roles', 'RolesController');

        /*routes for News*/


        Route::get(
            'crop_demo',
            function () {
                return redirect('admin/imagecropping');
            }
        );

        /* laravel example routes */
        // Charts
        Route::get('laravel_charts', 'ChartsController@index')->name('laravel_charts');
        Route::get('database_charts', 'ChartsController@databaseCharts')->name('database_charts');

        // datatables
        Route::get('datatables', 'DataTablesController@index')->name('index');
        Route::get('datatables/data', 'DataTablesController@data')->name('datatables.data');

        // datatables
        Route::get('jtable/index', 'JtableController@index')->name('index');
        Route::post('jtable/store', 'JtableController@store')->name('store');
        Route::post('jtable/update', 'JtableController@update')->name('update');
        Route::post('jtable/delete', 'JtableController@destroy')->name('delete');

        // SelectFilter
        Route::get('selectfilter', 'SelectFilterController@index')->name('selectfilter');
        Route::get('selectfilter/find', 'SelectFilterController@filter')->name('selectfilter.find');
        Route::post('selectfilter/store', 'SelectFilterController@store')->name('selectfilter.store');

        // editable datatables
        Route::get('editable_datatables', 'EditableDataTablesController@index')->name('index');
        Route::get('editable_datatables/data', 'EditableDataTablesController@data')->name('editable_datatables.data');
        Route::post('editable_datatables/create', 'EditableDataTablesController@store')->name('store');
        Route::post('editable_datatables/{id}/update', 'EditableDataTablesController@update')->name('update');
        Route::get('editable_datatables/{id}/delete', 'EditableDataTablesController@destroy')->name('editable_datatables.delete');

        //    # custom datatables
        Route::get('custom_datatables', 'CustomDataTablesController@index')->name('index');
        Route::get('custom_datatables/sliderData', 'CustomDataTablesController@sliderData')->name('custom_datatables.sliderData');
        Route::get('custom_datatables/radioData', 'CustomDataTablesController@radioData')->name('custom_datatables.radioData');
        Route::get('custom_datatables/selectData', 'CustomDataTablesController@selectData')->name('custom_datatables.selectData');
        Route::get('custom_datatables/buttonData', 'CustomDataTablesController@buttonData')->name('custom_datatables.buttonData');
        Route::get('custom_datatables/totalData', 'CustomDataTablesController@totalData')->name('custom_datatables.totalData');

        //tasks section
        Route::post('task/create', 'TaskController@store')->name('store');
        Route::get('task/data', 'TaskController@data')->name('data');
        Route::post('task/{task}/edit', 'TaskController@update')->name('update');
        Route::post('task/{task}/delete', 'TaskController@delete')->name('delete');




    }
);

// Remaining pages will be called from below controller method
// in real world scenario, you may be required to define all routes manually

Route::group(
    ['prefix' => 'admin', 'middleware' => 'admin'],
    function () {
        Route::get('{name?}', 'JoshController@showView');
    }
);

// FrontEndController
Route::get('login', 'FrontEndController@getLogin')->name('login');
Route::post('login', 'FrontEndController@postLogin')->name('login');
Route::get('register', 'FrontEndController@getRegister')->name('register');
Route::post('register', 'FrontEndController@postRegister')->name('register');
Route::get('activate/{userId}/{activationCode}', 'FrontEndController@getActivate')->name('activate');
Route::get('forgot-password', 'FrontEndController@getForgotPassword')->name('forgot-password');
Route::post('forgot-password', 'FrontEndController@postForgotPassword');

// Social Logins
Route::get('facebook', 'Admin\FacebookAuthController@redirectToProvider');
Route::get('facebook/callback', 'Admin\FacebookAuthController@handleProviderCallback');

Route::get('linkedin', 'Admin\LinkedinAuthController@redirectToProvider');
Route::get('linkedin/callback', 'Admin\LinkedinAuthController@handleProviderCallback');

Route::get('google', 'Admin\GoogleAuthController@redirectToProvider');
Route::get('google/callback', 'Admin\GoogleAuthController@handleProviderCallback');

// Forgot Password Confirmation
Route::post('forgot-password/{userId}/{passwordResetCode}', 'FrontEndController@postForgotPasswordConfirm');
Route::get('forgot-password/{userId}/{passwordResetCode}', 'FrontEndController@getForgotPasswordConfirm')->name('forgot-password-confirm');
// My account display and update details
Route::group(
    ['middleware' => 'user'],
    function () {
        Route::put('my-account', 'FrontEndController@update');
        Route::get('my-account', 'FrontEndController@myAccount')->name('my-account');
    }
);
// Email System
Route::group(
    ['prefix' => 'user_emails'],
    function () {
        Route::get('compose', 'UsersEmailController@create');
        Route::post('compose', 'UsersEmailController@store');
        Route::get('inbox', 'UsersEmailController@inbox');
        Route::get('sent', 'UsersEmailController@sent');
        Route::get('{email}', ['as' => 'user_emails.show', 'uses' => 'UsersEmailController@show']);
        Route::get('{email}/reply', ['as' => 'user_emails.reply', 'uses' => 'UsersEmailController@reply']);
        Route::get('{email}/forward', ['as' => 'user_emails.forward', 'uses' => 'UsersEmailController@forward']);
    }
);
Route::resource('user_emails', 'UsersEmailController');
Route::get('logout', 'FrontEndController@getLogout')->name('logout');
// contact form
Route::post('contact', 'FrontEndController@postContact')->name('contact');

// frontend views
Route::get(
    '/',
    ['as' => 'home', function () {
        return view('index2');
    },
    ]
);


Route::get('{name?}', 'FrontEndController@showFrontEndView');
// End of frontend views



Route::get('/apps', [AppController::class, 'index'])->name('apps.index');
Route::post('/apps', [AppController::class, 'store'])->name('apps.store');
