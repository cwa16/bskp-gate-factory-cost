<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class CheckTokenInQuery
{
    public function handle(Request $request, Closure $next)
    {
         if ($request->has('token')) {
        $token = $request->query('token');
        $appId = $request->query('app_id');

        $response = Http::withToken($token)->get("http://192.168.99.202/bskp-gate/public/api/profile?app_id=$appId");

        if (!$response->ok()) {
            abort(401, 'Token tidak valid.');
        }

        $data = $response->json();
        $user = User::firstWhere('email', $data['email']);

        if (!$user) {
            abort(403, 'User tidak ditemukan di aplikasi ini.');
        }

        Auth::login($user);
    }

    return $next($request);
    }

}
