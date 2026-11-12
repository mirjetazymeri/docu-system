<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySelectedLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('laravellocalization.supportedLocales', []));
        $prefix = $request->segment(1);
        $locale = in_array($prefix, $supported, true)
            ? $prefix
            : $request->session()->get('locale', config('app.locale'));

        if (!in_array($locale, $supported, true)) {
            $locale = config('app.locale');
        }

        // The localization package skips POST/PUT/PATCH/DELETE redirects.
        // Apply the saved selection to validation and flash messages as well.
        app('laravellocalization')->setLocale($locale);
        app('translator')->setFallback($locale);
        $request->session()->put('locale', $locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }
}
