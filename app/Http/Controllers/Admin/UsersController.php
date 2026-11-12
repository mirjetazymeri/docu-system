<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\JoshController;
use App\Http\Requests\UserRequest;
use App\Mail\Register;
use App\Mail\Restore;
use App\Models\AccessLevel;
use App\Models\Country;
use App\Models\User;
use Cartalyst\Sentinel\Checkpoints\LoginRequiredException;
use Cartalyst\Sentinel\Checkpoints\PasswordRequiredException;
use Cartalyst\Sentinel\Users\UserExistsException;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use File;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Redirect;
use Sentinel;
use URL;
use Validator;
use Throwable;
use Yajra\DataTables\DataTables;

class UsersController extends JoshController
{
    protected $countries;
    /**
     * Show a list of all the users.
     *
     * @return View
     */
    private $user_activation = false;
    public function __construct()
    {
        $this->countries = Country::all()->mapWithKeys(fn ($country) => [
            $country->sortname => \Illuminate\Support\Facades\Lang::hasForLocale('countries.'.strtoupper($country->sortname), app()->getLocale())
                ? __('countries.'.strtoupper($country->sortname))
                : $country->name,
        ])->toArray();
        // $this->middleware('sentinelAccess:users.view')->only(['index', 'show']);
        // $this->middleware('sentinelAccess:users.create')->only(['create', 'store']);
        // $this->middleware('sentinelAccess:users.edit')->only(['edit', 'update']);
        // $this->middleware('sentinelAccess:users.delete')->only('destroy');
    }

    public function index()
    {

        // Show the page
        return view('admin.users.index');
    }

    /*
     * Pass data through ajax call
     */
    /**
     * @return mixed
     */
    public function data()
    {
        $users = User::get(['id', 'first_name', 'last_name', 'email','created_at']);
        return DataTables::of($users)
            ->editColumn(
        'created_at',
        function (User $user) {
            return $user->created_at->diffForHumans();
        }
    )
            ->addColumn(
                'status',
                function ($user) {
                    if ($activation = Activation::completed($user)) {
                        return __('ui.activated');
                    }
                    return __('ui.pending');


                }
            )
            ->addColumn(
                'actions',
                function ($user) {
                    $actions = '<a href='. route('admin.users.show', $user->id) .'><i class="livicon" data-name="info" data-size="18" data-loop="true" data-c="#428BCA" data-hc="#428BCA" title="'.e(__('ui.view_user')).'"></i></a>
                            <a href='. route('admin.users.edit', $user->id) .'><i class="livicon" data-name="edit" data-size="18" data-loop="true" data-c="#428BCA" data-hc="#428BCA" title="'.e(__('ui.update_user')).'"></i></a>';
                    if ((Sentinel::getUser()->id !== $user->id) && ($user->id > 2)) {
                        $actions .= '<a href='. route('admin.users.confirm-delete', $user->id) .' data-id="'.$user->id.'" data-toggle="modal" data-target="#delete_confirm"><i class="livicon" data-name="user-remove" data-size="18" data-loop="true" data-c="#f56954" data-hc="#f56954" title="'.e(__('ui.delete_user')).'"></i></a>';
                    }
                    return $actions;
                }
            )
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Create new user
     *
     * @return View
     */
    // public function create()
    // {
    //     // Get all the available roles
    //     $roles = Sentinel::getRoleRepository()->all();
    //     $countries = $this->countries;
    //     // Show the page
    //     return view('admin.users.create', compact('roles', 'countries'));
    // }
    public function create()
    {
        // Get all the available roles
        $roles = Sentinel::getRoleRepository()->all();
        $countries = $this->countries;

        // Get all access levels
        $accessLevels = AccessLevel::all();

        // Show the page
        return view('admin.users.create', compact('roles', 'countries', 'accessLevels'));
    }

    /**
     * User create form processing.
     *
     * @return Redirect
     */
    public function store(UserRequest $request)
    {
        /*
         * Old versions of the application used soft deletes. Clear an old
         * trashed account with the same email before Sentinel registers the
         * replacement, otherwise the database unique index blocks it.
         */
        $oldDeletedUser = User::onlyTrashed()
            ->where('email', $request->input('email'))
            ->first();

        if ($oldDeletedUser) {
            $this->permanentlyDeleteUser($oldDeletedUser);
        }

        //upload image
        if ($file = $request->file('pic_file')) {
            $extension = $file->extension() ?: 'png';
            $destinationPath = public_path() . '/uploads/users/';
            $safeName = Str::random(10) . '.' . $extension;
            $file->move($destinationPath, $safeName);
            $request['pic'] = url('/').'/uploads/users/'.$safeName;
        }
        //check whether use should be activated by default or not
        $activate = $request->get('activate') ? true : false;
        try {
            // Register the user
            $user = Sentinel::register(
                $request->except(
                    '_token',
                    'password_confirm',
                    'role',
                    'activate',
                    'pic_file',
                    'g-recaptcha-response',
                    'access_level_ids'
                ),
                $activate
            );
            //add user to 'User' role
            $role = Sentinel::findRoleById($request->get('role'));
            if ($role) {
                $role->users()->attach($user);
            }

            $user->accessLevels()->sync(
                $request->input('access_level_ids', [])
            );
            //check for activation and send activation mail if not activated by default
            if (! $request->get('activate')) {
                // Data to be used on the email view
                $data = [
                    'user_name' => $user->first_name .' '. $user->last_name,
                    'activationUrl' => URL::route('activate', [$user->id, Activation::create($user)->code]),
                ];
                // Send the activation code through email
                try {
                    Mail::to($user->email)
                        ->send(new Register($data));
                } catch (Throwable $mailException) {
                    Log::warning('User created but activation email failed', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'message' => $mailException->getMessage(),
                    ]);

                    return Redirect::route('admin.users.index')
                        ->with('warning', __('ui.the_user_was_created_but_the_activation_email_could_not_be_sent'));
                }
            }
            // Activity log for New user create
            activity($user->full_name)
                ->performedOn($user)
                ->causedBy($user)
                ->log('New User Created by '.Sentinel::getUser()->full_name);
            // Redirect to the home page with success menu
            return Redirect::route('admin.users.index')->with('success', trans('users/message.success.create'));
        } catch (LoginRequiredException $e) {
            $error = trans('users/message.user_login_required');
        } catch (PasswordRequiredException $e) {
            $error = trans('users/message.user_password_required');
        } catch (UserExistsException $e) {
            $error = trans('users/message.user_exists');
        } catch (Throwable $e) {
            Log::error('User creation failed', [
                'email' => $request->input('email'),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $error = __('ui.the_user_could_not_be_created_check_the_details_and_try_again');
        }

        // Redirect to the user creation page
        return Redirect::back()->withInput()->with('error', $error);
    }

    /**
     * User update.
     *
     * @param  int $id
     *
     * @return View
     */
    // public function edit(User $user)
    // {

    //     // Get this user roles
    //     $userRoles = $user->getRoles()->pluck('name', 'id')->all();
    //     // Get a list of all the available roles
    //     $roles = Sentinel::getRoleRepository()->all();
    //     $status = Activation::completed($user);
    //     $countries = $this->countries;
    //     // Show the page
    //     return view('admin.users.edit', compact('user', 'roles', 'userRoles', 'countries', 'status'));
    // }
    public function edit(User $user)
    {
        // Get this user's roles
        $userRoles = $user->getRoles()->pluck('name', 'id')->all();

        // Get a list of all available roles
        $roles = Sentinel::getRoleRepository()->all();

        // Get all available access levels
        $accessLevels = AccessLevel::all();

        // Get this user's current access levels as an array of IDs
        $userAccessLevels = $user->accessLevels()->pluck('access_levels.id')->toArray();

        $status = Activation::completed($user);
        $countries = $this->countries;

        // Show the page
        return view('admin.users.edit', compact('user', 'roles', 'userRoles', 'countries', 'status', 'accessLevels', 'userAccessLevels'));
    }



    /**
     * User update form processing page.
     *
     * @param  User        $user
     * @param  UserRequest $request
     *
     * @return Redirect
     */
    public function update(User $user, UserRequest $request)
    {
        try {
            $user->update($request->except('pic_file', 'password', 'password_confirm', 'roles', 'activate'));
            if (! empty($request->password)) {
                $user->password = Hash::make($request->password);
            }

            // is new image uploaded?
            if ($file = $request->file('pic_file')) {
                $extension = $file->extension() ?: 'png';
                $destinationPath = public_path() . '/uploads/users/';
                $safeName = Str::random(10) . '.' . $extension;
                $file->move($destinationPath, $safeName);
                //delete old pic if exists
                if (File::exists($destinationPath . $user->pic)) {
                    File::delete($destinationPath . $user->pic);
                }
                //save new file path into db
                $user->pic = url('/').'/uploads/users/'.$safeName;
            }

            //save record
            $user->save();
            // Get the current user roles
            $userRoles = $user->roles()->pluck('id')->all();
            // Get the selected roles

            $selectedRoles = $request->get('roles');
            // Roles comparison between the roles the user currently
            // have and the roles the user wish to have.
            $rolesToAdd = array_diff($selectedRoles, $userRoles);
            $rolesToRemove = array_diff($userRoles, $selectedRoles);
            // Assign the user to roles

            foreach ($rolesToAdd as $roleId) {
                $role = Sentinel::findRoleById($roleId);
                $role->users()->attach($user);
            }

            // Remove the user from roles
            foreach ($rolesToRemove as $roleId) {
                $role = Sentinel::findRoleById($roleId);
                $role->users()->detach($user);
            }


        // Update access levels
        if ($request->has('access_level_ids')) {
            $user->accessLevels()->sync($request->get('access_level_ids'));
        } else {
            $user->accessLevels()->detach(); // Detach all access levels if none selected
        }

            // Activate / De-activate user

            $status = $activation = Activation::completed($user);
            if ($request->get('activate') !== $status) {
                if ($request->get('activate')) {
                    $activation = Activation::exists($user);
                    if ($activation) {
                        Activation::complete($user, $activation->code);
                    }
                } else {
                    //remove existing activation record
                    Activation::remove($user);
                    //add new record
                    Activation::create($user);
                    //send activation mail
                    $data = [
                        'user_name' => $user->first_name .' '. $user->last_name,
                        'activationUrl' => URL::route('activate', [$user->id, Activation::exists($user)->code]),
                    ];
                    // Send the activation code through email
                    Mail::to($user->email)
                        ->send(new Restore($data));
                }
            }

            // Was the user updated?
            if ($user->save()) {
                // Prepare the success message
                $success = trans('users/message.success.update');
                //Activity log for user update
                activity($user->full_name)
                    ->performedOn($user)
                    ->causedBy($user)
                    ->log('User Updated by '.Sentinel::getUser()->full_name);
                // Redirect to the user page
                return Redirect::route('admin.users.edit', $user)->with('success', $success);
            }

            // Prepare the error message
            $error = trans('users/message.error.update');
        } catch (UserNotFoundException $e) {
            // Prepare the error message
            $error = trans('users/message.user_not_found', compact('id'));
            // Redirect to the user management page
            return Redirect::route('admin.users.index')->with('error', $error);
        }

        // Redirect to the user page
        return Redirect::route('admin.users.edit', $user)->withInput()->with('error', $error);
    }

    /**
     * Show a list of all the deleted users.
     *
     * @return View
     */
    public function getDeletedUsers()
    {
        // Grab deleted users
        $users = User::onlyTrashed()->get();
        // Show the page
        return view('admin.deleted_users', compact('users'));
    }

    /**
     * Delete Confirm
     *
     * @param  int $id
     *
     * @return View
     */
    public function getModalDelete($id)
    {
        $model = 'users';
        $confirm_route = $error = null;
        try {
            // Get user information
            $user = Sentinel::findById($id);
            // Check if we are not trying to delete ourselves
            if ($user->id === Sentinel::getUser()->id) {
                // Prepare the error message
                $error = trans('users/message.error.delete');
                return view('admin.layouts.modal_confirmation', compact('error', 'model', 'confirm_route'));
            }
        } catch (UserNotFoundException $e) {
            // Prepare the error message
            $error = trans('users/message.user_not_found', compact('id'));
            return view('admin.layouts.modal_confirmation', compact('error', 'model', 'confirm_route'));
        }
        $confirm_route = route('admin.users.delete', ['id' => $user->id]);
        return view('admin.layouts.modal_confirmation', compact('error', 'model', 'confirm_route'));
    }

    /**
     * Delete the given user.
     *
     * @param  int $id
     *
     * @return Redirect
     */
    public function destroy($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            $currentUser = Sentinel::getUser();

            if ($user->id === $currentUser->id) {
                $error = trans('users/message.error.delete');

                return Redirect::route('admin.users.index')->with('error', $error);
            }

            $deletedUserName = $user->full_name;
            $this->permanentlyDeleteUser($user);

            activity($deletedUserName)
                ->causedBy($currentUser)
                ->withProperties(['deleted_user_id' => (int) $id])
                ->log('User permanently deleted by ' . $currentUser->full_name);

            $success = trans('users/message.success.delete');

            return Redirect::route('admin.users.index')->with('success', $success);
        } catch (Throwable $exception) {
            Log::error('Permanent user deletion failed', [
                'user_id' => $id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            $error = trans('users/message.error.delete');

            return Redirect::route('admin.users.index')->with('error', $error);
        }
    }

    /**
     * Return a safe absolute path for an uploaded user picture.
     */
    private function getUserPicturePath(?string $pictureUrl): ?string
    {
        if (!$pictureUrl) {
            return null;
        }

        $urlPath = str_replace(
            '\\',
            '/',
            (string) parse_url($pictureUrl, PHP_URL_PATH)
        );

        if (!str_contains($urlPath, '/uploads/users/')) {
            return null;
        }

        $filename = basename($urlPath);

        if (!$filename || in_array($filename, ['.', '..'], true)) {
            return null;
        }

        return public_path('uploads/users/' . $filename);
    }

    /**
     * Permanently remove a user and all authentication/assignment records.
     */
    private function permanentlyDeleteUser(User $user): void
    {
        $picturePath = $this->getUserPicturePath($user->pic);

        DB::transaction(function () use ($user) {
            DB::table('activations')->where('user_id', $user->id)->delete();
            DB::table('persistences')->where('user_id', $user->id)->delete();
            DB::table('reminders')->where('user_id', $user->id)->delete();
            DB::table('throttle')->where('user_id', $user->id)->delete();
            DB::table('role_users')->where('user_id', $user->id)->delete();
            DB::table('user_access_levels')->where('user_id', $user->id)->delete();
            DB::table('application_user')->where('user_id', $user->id)->delete();

            $user->forceDelete();
        });

        if ($picturePath && File::exists($picturePath)) {
            File::delete($picturePath);
        }
    }

    /**
     * Restore a deleted user.
     *
     * @param  int $id
     *
     * @return Redirect
     */
    public function getRestore($id)
    {
        try {
            // Get user information
            $user = User::withTrashed()->find($id);
            // Restore the user
            $user->restore();
            // create activation record for user and send mail with activation link
            //            $data->user_name = $user->first_name .' '. $user->last_name;
            //            $data->activationUrl = URL::route('activate', [$user->id, Activation::create($user)->code]);
            // Send the activation code through email
            $data = [
                'user_name' => $user->first_name .' '. $user->last_name,
                'activationUrl' => URL::route('activate', [$user->id, Activation::create($user)->code]),
            ];
            Mail::to($user->email)
                ->send(new Restore($data));
            // Prepare the success message
            $success = trans('users/message.success.restored');
            activity($user->full_name)
                ->performedOn($user)
                ->causedBy($user)
                ->log('User restored by '.Sentinel::getUser()->full_name);
            // Redirect to the user management page
            return Redirect::route('admin.deleted_users')->with('success', $success);
        } catch (UserNotFoundException $e) {
            // Prepare the error message
            $error = trans('users/message.user_not_found', compact('id'));
            // Redirect to the user management page
            return Redirect::route('admin.deleted_users')->with('error', $error);
        }
    }

    /**
     * Display specified user profile.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        try {
            // Get the user information
            $user = Sentinel::findUserById($id);
            //get country name
            if ($user->country) {
                $user->country = $this->countries[$user->country];
            }
        } catch (UserNotFoundException $e) {
            // Prepare the error message
            $error = trans('users/message.user_not_found', compact('id'));
            // Redirect to the user management page
            return Redirect::route('admin.users.index')->with('error', $error);
        }
        // Show the page
        return view('admin.users.show', compact('user'));
    }

    public function passwordreset(Request $request)
    {
        $id = $request->id;
        $user = Sentinel::findUserById($id);
        $password = $request->get('password');
        $user->password = Hash::make($password);
        $user->save();
    }

    public function import()
    {
        return view('admin.users.import_users');
    }
    public function downloadExcel($type)
    {
        return response()->download(base_path('resources/excel-templates/users.xlsx'));
    }

    public function importInsert(Request $request)
    {
        if ($request->hasFile('import_file')) {
            $activate = $this->user_activation;
            $path = $request->file('import_file')->getRealPath();
            $users = Excel::selectSheets('users')->load(
    $path,
    function ($reader) {
    }
)->get();
            if (! empty($users) && $users->count()) {
                foreach ($users->toArray() as $key => $row) {
                    $my_data = [
                        'email' => $row['email'],
                        'first_name' => $row['first_name'],
                        'last_name' => $row['last_name'],
                        'password' => $row['password'],
                    ];
                    $validator = Validator::make(
    $my_data,
    [
                            'email' => 'email',
                            'first_name' => 'required|min:3',
                            'last_name' => 'required|min:3',
                            'password' => 'required|min:3',
                        ]
);
                    if (isset($row['first_name']) && trim($row['first_name']) !== '' && ! $validator->fails()) {
                        $selected_user = User::where(
                            [
                                ['email', $row['email']],
                            ]
                        )->first();
                        if ($selected_user) {
                            $user = $selected_user->update(
                                [
                                    'email' => $row['email'],
                                    'first_name' => $row['first_name'],
                                    'last_name' => $row['last_name'],
                                    'password' => Hash::make($row['password']),
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ]
                            );
                            activity($selected_user->full_name)
                                ->performedOn($selected_user)
                                ->causedBy($selected_user)
                                ->log('User Updated by '.Sentinel::getUser()->full_name);
                        } else {
                            $user = Sentinel::register(
                                [
                                    'email' => $row['email'],
                                    'first_name' => $row['first_name'],
                                    'last_name' => $row['last_name'],
                                    'password' => Hash::make($row['password']),
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ],
                                $activate
                            );
                            activity($user->full_name)
                                ->performedOn($user)
                                ->causedBy($user)
                                ->log('User Registered by '.Sentinel::getUser()->full_name);
                            // login user automatically
                            $role = Sentinel::findRoleById(2);
                            //add user to 'User' role

                            $role->users()->attach($user);
                            if (! $activate) {
                                // Data to be used on the email view

                                $data = [
                                    'user_name' => $user->first_name .' '. $user->last_name,
                                    'activationUrl' => URL::route('activate', [$user->id, Activation::create($user)->code]),
                                ];
                                // Send the activation code through email
                                Mail::to($user->email)->send(new Register($data));
                            }
                        }
                    }
                }

                return back()->with('success', __('ui.inserted_record_successfully'));
            }
        }
        return back()->with('error', __('ui.please_check_your_file_something_is_wrong_there'));
    }
}
