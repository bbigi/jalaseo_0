<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyUserPreferences
{
    /**
     * Apply user locale and timezone on every web request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if (!empty($user->locale)) {
                $locale = str_replace('-', '_', $user->locale);
                // Normalize long locales (e.g., id_ID -> id) to match available lang folders
                if (preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale)) {
                    $locale = substr($locale, 0, 2);
                }
                app()->setLocale($locale);
            }

            if (!empty($user->timezone)) {
                @date_default_timezone_set($user->timezone);
            }
        }

        return $next($request);
    }
}
