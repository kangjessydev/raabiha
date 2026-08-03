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

        // 1. Secara default, hanya Role Kasir yang langsung diizinkan
        if ($user->hasRole('kasir') || $user->role === 'kasir') {
            return $next($request);
        }

        // 2. Cek apakah User ID terdaftar di Whitelist Akses POS
        $rawWhitelist = \App\Models\SiteSetting::where('key', 'pos_allowed_user_ids')->value('value');
        $allowedUserIds = is_string($rawWhitelist) ? (json_decode($rawWhitelist, true) ?: []) : (is_array($rawWhitelist) ? $rawWhitelist : []);
        $allowedUserIds = array_map('strval', $allowedUserIds);

        if (in_array((string)$user->id, $allowedUserIds, true)) {
            return $next($request);
        }

        // Jika user tidak ber-role kasir dan tidak di-whitelist, alihkan ke Admin Panel / Login
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Akun Anda tidak memiliki hak akses ke Terminal POS Kasir.'], 403);
        }

        return redirect('/admin')->with('error', 'Akun Anda tidak memiliki hak akses ke Terminal POS Kasir. Minta Admin mendaftarkan akun Anda di Whitelist POS.');
    }
}
