<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\ApiToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
   public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = Str::random(60);
    $user->apiTokens()->create(['token' => hash('sha256', $token)]);

    return response()->json([
        'success' => true,
        'data' => [
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'location'   => $user->location,
                'avatar'     => $user->avatar ?? null,
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
            ],
            'token' => $token
        ]
    ]);
}
public function logout(Request $request)
{
    $header = $request->header('Authorization');

        if (! $header || ! str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Token required'], 401);
        }

        $plainToken = substr($header, 7);
        $hashedToken = hash('sha256', $plainToken);

        $deleted = $request->user()?->apiTokens()->where('token', $hashedToken)->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => ' logged out successfully'
        ]);
    }
public function verifyClient(Request $request)
{
    $user = auth()->user();

    return response()->json([
        'success' => true,
        'data' => [
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'location' => $user->location,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]
    ]);
}


}
