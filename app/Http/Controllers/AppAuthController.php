<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;

class AppAuthController
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'error'   => 1,
                'message' => $validator->errors()->first(),
                'data'    => null
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => 0,
                    'error'   => 1,
                    'message' => 'Invalid credentials',
                    'data'    => null
                ], 401);
            }

            $user = JWTAuth::user();

            if (!$user->hasRole('Staff')) {
                return response()->json([
                    'success' => 0,
                    'error'   => 1,
                    'message' => 'Only staff users are allowed to log in',
                    'data'    => null
                ], 403);
            }

        } catch (JWTException $e) {
            return response()->json([
                'success' => 0,
                'error'   => 1,
                'message' => 'Could not create token',
                'data'    => null
            ], 500);
        }

        return response()->json([
            'success' => 1,
            'error'   => 0,
            'message' => 'Login successful',
            'data'    => [
                'token' => $token,
                'user'  => $user
            ]
        ], 200);
    }
}