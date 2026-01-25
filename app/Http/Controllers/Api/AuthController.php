<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'code' => 401,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // optional: hapus token lama user
        ApiToken::where('user_id', $user->id)->delete();

        $token = Str::random(40);

        ApiToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expired_at' => now()->addMinutes(30),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'expires_in' => 1800,
                'user' => [
                    'id' => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email
                ]
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->header('nds-token');

        ApiToken::where('token', $token)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out'
        ]);
    }
}
