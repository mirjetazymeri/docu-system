<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\JoshController;
use App\Http\Requests\ConfirmPasswordRequest;
use App\Http\Requests\ForgotRequest;
use App\Http\Requests\UserRequest;
use App\Mail\ForgotPassword;
use App\Mail\Register;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Mail;
use Reminder;
use Sentinel;
use stdClass;
use URL;
use View;

class AuthController extends JoshController
{
    /**
     * Account sign in.
     *
     * @return View
     */

    private $user_activation = false;

    public function getSignin()
    {
        // Is the user logged in?
        if (Sentinel::check()) {
            if ($this->isDocumentationWorker(Sentinel::getUser())) {
                return Redirect::route('applications.index');
            }
            return Redirect::route('admin.dashboard');
        }

        // Show the page
        return view('admin.login');
    }

    /**
     * Account sign in form processing.
     *
     * @param  Request $request
     *
     * @return Redirect
     */
    public function postSignin(Request $request)
    {
        try {
            // Try to log the user in
            if ($user = Sentinel::authenticate($request->only(['email', 'password']), $request->get('remember-me', 0))) {
                // Redirect to the dashboard page
                //Activity log
                activity($user->full_name)
                    ->performedOn($user)
                    ->causedBy($user)
                    ->log('LoggedIn');
                //activity log ends
                if ($this->isDocumentationWorker($user)) {
                    return Redirect::route('applications.index')
                        ->with('success', trans('auth/message.signin.success'));
                }

                return Redirect::route('admin.dashboard')->with('success', trans('auth/message.signin.success'));
            }

            $this->messageBag->add('email', trans('auth/message.account_not_found'));
        } catch (NotActivatedException $e) {
            $this->messageBag->add('email', trans('auth/message.account_not_activated'));
        } catch (ThrottlingException $e) {
            $delay = $e->getDelay();
            $this->messageBag->add('email', trans('auth/message.account_suspended', compact('delay')));
        }

        // Ooops.. something went wrong
        return Redirect::back()->withInput()->withErrors($this->messageBag);
    }

    private function isDocumentationWorker($user): bool
    {
        return $user && $user->accessLevels()
            ->where('access_levels.name', 'Documentation Worker')
            ->exists();
    }

    /**
     * Account sign up form processing.
     *
     * @return Redirect
     */
    public function postSignup(UserRequest $request)
    {
        $activate = $this->user_activation;

        try {
            // Register the user
            $user = Sentinel::register(
                [
                    'first_name' => $request->get('first_name'),
                    'last_name' => $request->get('last_name'),
                    'email' => $request->get('email'),
                    'password' => $request->get('password'),
                ],
                $activate
            );

            $role = Sentinel::findRoleById(2);

            $role->users()->attach($user);
            if (! $activate) {

                $data = [
                    'user_name' => $user->first_name .' '. $user->last_name,
                    'activationUrl' => URL::route('activate', [$user->id, Activation::create($user)->code]),
                ];
                Mail::to($user->email)
                    ->send(new Register($data));

                return redirect('admin.login')->with('success', trans('auth/message.signup.success'));
            }
            Sentinel::login($user, false);

            activity($user->full_name)
                ->performedOn($user)
                ->causedBy($user)
                ->log('Registered');
            return Redirect::route('admin.dashboard')->with('success', trans('auth/message.signup.success'));
        } catch (UserExistsException $e) {
            $this->messageBag->add('email', trans('auth/message.account_already_exists'));
        }

        return Redirect::back()->withInput()->withErrors($this->messageBag);
    }

    /**
     * User account activation page.
     *
     * @param  number $userId
     * @param  string $activationCode
     *
     * @return
     */
    public function getActivate($userId, $activationCode = null)
    {
        if (Sentinel::check()) {
            return Redirect::route('admin.dashboard');
        }

        $user = Sentinel::findById($userId);
        $activation = Activation::create($user);

        if (Activation::complete($user, $activation->code)) {
            return Redirect::route('signin')->with('success', trans('auth/message.activate.success'));
        }
        $error = trans('auth/message.activate.error');
        return Redirect::route('signin')->with('error', $error);


    }

    /**
     * Forgot password form processing page.
     *
     * @param Request $request
     *
     * @return Redirect
     */
    public function postForgotPassword(ForgotRequest $request)
    {
        $data = new stdClass();

        try {
            $user = Sentinel::findByCredentials(['email' => $request->get('email')]);

            if (! $user) {
                return back()->with('error', trans('auth/message.account_email_not_found'));
            }
            $activation = Activation::completed($user);
            if (! $activation) {
                return back()->with('error', trans('auth/message.account_not_activated'));
            }
            $reminder = Reminder::create($user);
            $data->user_name = $user->first_name .' ' .$user->last_name;
            $data->forgotPasswordUrl = URL::route('forgot-password-confirm', [$user->id, $reminder->code]);



            Mail::to($user->email)
                ->send(new ForgotPassword($data));
        } catch (UserNotFoundException $e) {

        }


        return back()->with('success', trans('auth/message.forgot-password.success'));
    }

    /**
     * Forgot Password Confirmation page.
     *
     * @param  number $userId
     * @param  string $passwordResetCode
     *
     * @return View
     */
    public function getForgotPasswordConfirm($userId, $passwordResetCode = null)
    {

        if (! $user = Sentinel::findById($userId)) {

            return Redirect::route('forgot-password')->with('error', trans('auth/message.account_not_found'));
        }
        if ($reminder = Reminder::exists($user)) {
            if ($passwordResetCode === $reminder->code) {
                return view('admin.auth.forgot-password-confirm');
            }
            return 'code does not match';


        }
        return 'does not exists';


    }

    /**
     * Forgot Password Confirmation form processing page.
     *
     * @param  Request $request
     * @param  number  $userId
     * @param  string  $passwordResetCode
     *
     * @return Redirect
     */
    public function postForgotPasswordConfirm(ConfirmPasswordRequest $request, $userId, $passwordResetCode = null)
    {

        $user = Sentinel::findById($userId);
        if (! $reminder = Reminder::complete($user, $passwordResetCode, $request->get('password'))) {

            return Redirect::route('signin')->with('error', trans('auth/message.forgot-password-confirm.error'));
        }


        return Redirect::route('signin')->with('success', trans('auth/message.forgot-password-confirm.success'));
    }

    /**
     * Logout page.
     *
     * @return Redirect
     */
    public function getLogout()
    {
        if (Sentinel::check()) {

            $user = Sentinel::getuser();
            activity($user->full_name)
                ->performedOn($user)
                ->causedBy($user)
                ->log('LoggedOut');

            Sentinel::logout();
        }

        return redirect('admin/signin')->with('success', __('ui.you_have_successfully_logged_out'));
    }

    /**
     * Account sign up form processing for register2 page
     *
     * @param Request $request
     *
     * @return Redirect
     */
    public function postRegister2(UserRequest $request)
    {
        try {

            $user = Sentinel::registerAndActivate(
                [
                    'first_name' => $request->get('first_name'),
                    'last_name' => $request->get('last_name'),
                    'email' => $request->get('email'),
                    'password' => $request->get('password'),

                ]
            );


            $role = Sentinel::findRoleById(2);
            $role->users()->attach($user);


            Sentinel::login($user, false);


            return Redirect::route('admin.dashboard')->with('success', trans('auth/message.signup.success'));
        } catch (UserExistsException $e) {
            $this->messageBag->add('email', trans('auth/message.account_already_exists'));
        }


        return Redirect::back()->withInput()->withErrors($this->messageBag);
    }
}
