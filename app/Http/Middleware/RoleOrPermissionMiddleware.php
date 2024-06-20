<?php

namespace App\Http\Middleware;

use App\Models\SystemModule;
use Closure;
use Spatie\Permission\Models\Role;
use App\Models\AdminUser;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next)
    {
  
        $bearerToken = $request->header('Authorization');
        $token = substr($bearerToken, 7);
        
        try {
            $user = JWTAuth::setToken($token)->toUser();
        } catch (JWTException $e) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Unauthorized',
                'data' => null
            ], 401);
        }

        if (!$user) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'User not found',
                'data' => null
            ], 401);
        }

        $userRoles = $user->roles;
        
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }
        $module = $request->header('action-module');
        $permission = $request->header('action-type');

        if (!$module || !$permission) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Missing module or permission headers',
                'data' => null
            ], 400);
        }
        $moduleExists = SystemModule::where('slug', $module)->exists();
        if (!$moduleExists) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Module not Present',
                'data' => null
            ], 403);
        }
        $roles = $userRoles->pluck('name')->toArray();
        $rolesWithPermissions = Role::whereIn('name', $roles)
            ->whereHas('permissions', function ($query) use ($module, $permission) {
                $query->where('name', $permission)
                    ->whereJsonContains('module', $module);
            })
            ->pluck('name')
            ->toArray();
        if (empty($rolesWithPermissions)) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'User doesn\'t have the required roles or permissions for the given module',
                'data' => $rolesWithPermissions
            ], 403);
        }

        return $next($request);
    }
}

