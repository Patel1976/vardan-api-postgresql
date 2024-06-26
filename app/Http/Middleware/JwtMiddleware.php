<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $bearerToken =  $request->header('Authorization');
        $token = substr($bearerToken, 7);
        // error_log($token);
        if (!$token) { 
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Token is not present',
                'data' => null
            ], 401);
        }

        try {
            JWTAuth::setToken($token)->authenticate();
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