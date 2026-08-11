<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocalizationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language') ?? app()->getLocale();

        // Support for am, en, om, ti
        $supported = ['am', 'en', 'om', 'ti'];
        if (!in_array($locale, $supported)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
