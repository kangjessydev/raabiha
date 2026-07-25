<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePosAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('pos.login');
        }

        $user = Auth::user();
        $allowedRoles = ['kasir', 'super_admin', 'owner', 'manager', 'finance'];

        if (!$user->hasAnyRole($allowedRoles) && !in_array($user->role, $allowedRoles)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('pos.login')->with('error', 'Akun Anda tidak memiliki hak akses ke Terminal POS Kasir.');
        }

        return $next($request);
    }
}
