<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTokenAuth
{

public function handle(Request $request, Closure $next)
{
    // Allow preflight
    if ($request->isMethod('OPTIONS')) {
        return response()->json([], 204);
    }

    $header = $request->header('Authorization');

    if (! $header || ! str_starts_with($header, 'Bearer ')) {
        return response()->json(['message' => 'Token required'], 401);
    }

    $plainToken = substr($header, 7);
    $hashedToken = hash('sha256', $plainToken);

    $token = ApiToken::where('token', $hashedToken)->first();

    if (! $token) {
        return response()->json(['message' => 'Invalid token'], 401);
    }

    // ✅ This sets the authenticated user globally
    Auth::setUser($token->user);

    return $next($request);
}
}
