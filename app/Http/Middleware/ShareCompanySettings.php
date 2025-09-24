<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Cache;

class ShareCompanySettings
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get company settings using the model's cached method
        $companySettings = CompanySetting::getPublic();

        // Share with Inertia
        Inertia::share([
            'companySettings' => $companySettings,
        ]);

        return $next($request);
    }
}