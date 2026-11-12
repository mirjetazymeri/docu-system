<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\AccessLevel;
use Illuminate\Support\Facades\Gate;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        if (!Sentinel::check()) {
            abort(403, 'Unauthorized action.');
        }

        // Get the authenticated user
        $user = Sentinel::getUser();

        // Retrieve all permissions for the authenticated user via Eloquent relationships
        $userPermissions = $user->accessLevels()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('slug')
            ->unique();

        // Debugging: dump the user permissions
        // dd($userPermissions);

        // Check if the required permission exists in the user's permissions
        if (!$userPermissions->contains($permission)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}

