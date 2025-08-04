<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\StaffUser;
use Illuminate\Support\Facades\Log;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $bearerToken = $request->header('Authorization');
        $token = $bearerToken ? substr($bearerToken, 7) : null;
        if (!$token) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Token is not present',
                'data' => null
            ], 401);
        }
        try {
            if ($request->is('api/app/*')) {
                $user = auth('staff')->setToken($token)->authenticate();
                if (!$user) {
                    return response()->json([
                        'success' => 0,
                        'error' => 1,
                        'message' => 'User not found',
                        'data' => null
                    ], 401);
                }
                if ($user->jwt_token !== $token) {
                    return response()->json([
                        'success' => 0,
                        'error' => 1,
                        'message' => 'Token is invalid. Please log in again.',
                        'data' => null
                    ], 401);
                }
            } 
            else {
                $user = JWTAuth::setToken($token)->authenticate();
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'JWT token is invalid',
                'data' => null
            ], 401);
        }
        return $next($request);
    }
}