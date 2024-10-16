<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string",
            "email" => ["required", "email", "unique:users",],
            "password" => ["required", "min:6"],
        ]);
        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => bcrypt($data["password"]),
        ]);
        $token = $user->createToken("auth_token")->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }


    public function login(Request $request)
    {
        $request->validate([
            "email" => ["required", "email", "exists:users",],
            "password" => ["required", "min:6"],
        ]);

        $credentials = request(['email', 'password']);
        if (!auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'The given data was invalid',
                'errors' => ['Invalid credentials'],
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $authToken = $user->createToken("auth_token")->plainTextToken;

        return [
            'access_token' => $authToken,
        ];
    }


}
