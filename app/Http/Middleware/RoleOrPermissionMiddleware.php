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
        // Log the incoming request data

        $bearerToken = $request->header('Authorization');
        $token = substr($bearerToken, 7);


        try {
            $user = JWTAuth::setToken($token)->toUser();
        } catch (JWTException $e) {
            error_log('JWT Exception: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Unauthorized',
                'data' => null
            ], 401);
        }

        if (!$user) {
            error_log('User not found');
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'User not found',
                'data' => null
            ], 401);
        }

        // Print user ID


        // Print user roles and their IDs
        $userRoles = $user->roles;
        foreach ($userRoles as $role) {
  
        }

        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }
            error_log($userRoles);
        $module = $request->header('action-module');
        $permission = $request->header('action-type');


        if (!$module || !$permission) {
            error_log('Missing module or permission headers');
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Missing module or permission headers',
                'data' => null
            ], 400);
        }

        // Check if the module exists in SystemModule
        $moduleExists = SystemModule::where('slug', $module)->exists();


        if (!$moduleExists) {
            error_log('Module not Present');
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Module not Present',
                'data' => null
            ], 403);
        }

        // Check user's roles and permissions
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

