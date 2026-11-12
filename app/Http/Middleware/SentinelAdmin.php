<?php

namespace App\Http\Middleware;

use App\Models\Task;
use Closure;
use Sentinel;

class SentinelAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! Sentinel::check()) {
            return redirect('admin/signin')->with('info', __('ui.you_must_be_logged_in'));
        }
        if (! Sentinel::inRole('admin')) {
            return redirect('my-account');
        }

        $tasks_count = Task::where('user_id', Sentinel::getUser()->id)->count();
        $request->attributes->add(['tasks_count' => $tasks_count]);

        return $next($request);
    }
}
