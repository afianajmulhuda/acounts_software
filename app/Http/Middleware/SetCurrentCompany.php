<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\Company;

class SetCurrentCompany
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            // dd($user);
            
            // Get companies user has access to
            $userCompanies = $user->companies;
            // dd($userCompanies);

            if ($userCompanies->isNotEmpty()) {
                // Determine current company from session or default to the first accessible
                $currentCompanyId = session('active_company_id', $userCompanies->first()->id);
                $currentCompany = $userCompanies->firstWhere('id', $currentCompanyId) ?? $userCompanies->first();

                // Store in session and share globally with Blade templates
                session(['active_company_id' => $currentCompany->id]);
                View::share('currentCompany', $currentCompany);
                View::share('userCompanies', $userCompanies);
            }
        }

        return $next($request);
    }
}