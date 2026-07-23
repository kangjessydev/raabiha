<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectKasirToPos
{
    /**
     * Handle an incoming request.
     *
     * Jika user hanya memiliki role 'kasir' (tanpa role admin/manajerial lain),
     * redirect langsung ke Layar POS (/pos).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->hasRole('kasir') && ! $user->hasAnyRole(['super_admin', 'owner', 'finance', 'marketing', 'logistics', 'cs'])) {
            return redirect()->route('pos.index');
        }

        return $next($request);
    }
}
