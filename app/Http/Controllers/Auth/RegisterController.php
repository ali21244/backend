<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\ApiToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'required|string|max:20',
            'location' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'location' => $request->location,
            'password' => Hash::make($request->password),
        ]);

        $token = Str::random(60);
        $user->apiTokens()->create(['token' => hash('sha256', $token)]);

        return response()->json([
            'token' => $token,
            'user'  => $user
        ], 201);
    }
}

