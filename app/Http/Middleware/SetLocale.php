<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * Check for a language header (or query parameter) and set the locale accordingly.
     */
    public function handle(Request $request, Closure $next)
    {
        // You can decide whether to read a header, query parameter, or both.
        // Here we check for an "Accept-Language" header; default to "en" if not present.
        $locale = $request->header('Accept-Language', 'en');

        // Optionally, validate the locale value.
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'en';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
