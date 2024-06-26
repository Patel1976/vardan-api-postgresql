<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminUser;
use App\Models\SystemModule;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleController
{
    // create roles
    public function createRole(Request $request)
    {
        try {
            $roleName = $request->role;
            if ($roleName) {
                Role::create(['name' => $roleName]);
                return response()->json([
                    'success' => 1,
                    'error' => 0,
                    'message' => $roleName . ' role created successfully',
                    'data' => null
                ], 201);
            } else {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Role name is required',
                    'data' => null
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // Edit Role Name
    public function updateRole(Request $request, $id)
{
    try {
        // Retrieve the role name from the request
        $roleName = $request->input('role');
        
        // Check if the role name is provided
        if (!$roleName) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Role name is required',
                'data' => null
            ], 400);
        }
        
        // Find the role by ID
        $findRole = Role::find($id);
        
       
        if ($findRole) {
            $findRole->name = $roleName;
            $findRole->save();
            
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => $roleName . ' role updated successfully',
                'data' => null
            ], 201);
        } else {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Role not found',
                'data' => null
            ], 404);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => 0,
            'error' => 1,
            'message' => $e->getMessage(),
            'data' => null
        ], 500);
    }
}

    //Get all Roles
    public function getAllRoles()
    {
        try {

            $findAllUsers = Role::all();
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Roles retrieved successfully',
                'data' => $findAllUsers
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Something went wrong',
                'data' => null
            ], 401);
        }
    }

    //Get role by id
    public function getRoleById($id)
    {
        try {
            $role = Role::findOrFail($id);
            if (!$role) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Role not found',
                    'data' => null
                ], 404);
            }
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Role found',
                'data' => $role
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => $e->getMessage(),
                'data' => null
            ], 401);
        }
    }

    // delete role
    public function deleteRole(Request $request)
    {
        try {
            $roleId = $request->id;
            $role = Role::findOrFail($roleId);
            $usersWithRole = $role->users()->count();
            if ($usersWithRole > 0) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Role is currently assigned to one or more users and cannot be deleted',
                    'data' => null
                ], 422);
            }
            if ($role->name == 'Super Admin') {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Role ' . $role->name . ' Can\'t be deleted ',
                    'data' => null
                ], 422);
            }
            $role->delete();
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Role ' . $role->name . ' deleted successfully',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Role not found or could not be deleted',
                'data' => null
            ], 404);
        }
    }

    //Fetch the roles with permission by ID
    public function getRoleWithPermissionById($id)
    {
        try {
            $roleWithPermissions = Role::with('permissions')->findOrFail($id);

            $rolePermissions = DB::table('role_has_permissions')
                ->where('role_id', $id)
                ->get();

            $permissionModuleMap = [];
            foreach ($rolePermissions as $rolePermission) {
                $permissionModuleMap[$rolePermission->permission_id] = $rolePermission->module;
            }

            foreach ($roleWithPermissions->permissions as $permission) {
                $permission->module = $permissionModuleMap[$permission->id] ?? [];
            }

            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => '',
                'data' => $roleWithPermissions
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Wrong ID is submitted or ID is invalid',
                'data' => null
            ], 400);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Something went wrong',
                'data' => null
            ], 500);
        }
    }

    //Assign  Role Permission By Id
    public function assignPermissionsToRoleById(Request $request, $roleId)
    {
        try {
            $permissionsArray = $request->input('permissions', []);
            $permissionChanges = [];

            foreach ($permissionsArray as $permission) {
                $module = $permission['module'];
                $permissionName = $permission['permission'];

                if (!isset($permissionChanges[$module])) {
                    $permissionChanges[$module] = [];
                }

                $permissionChanges[$module][] = $permissionName;
            }

            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Permissions assigned to role successfully',
                'data' => $permissionChanges
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Failed to assign permissions: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

     //get all Roles with permission List
     public function getAllRolesWithPermission()
     {
         try {
             $rolesWithPermissions = Role::with('permissions')->get();
             $rolePermissions = DB::table('role_has_permissions')->get();
             $permissionModuleMap = [];
             foreach ($rolePermissions as $rolePermission) {
                 $permissionModuleMap[$rolePermission->permission_id] = json_decode($rolePermission->module, true);
             }
             foreach ($rolesWithPermissions as $role) {
                 foreach ($role->permissions as $permission) {
                     if (isset($permissionModuleMap[$permission->id])) {
                         $permission->module = $permissionModuleMap[$permission->id];
                     } else {
                         $permission->module = [];
                     }
                 }
             }
             return response()->json([
                 'success' => 1,
                 'error' => 0,
                 'message' => '',
                 'data' => $rolesWithPermissions
             ], 200);
         } catch (\Throwable $th) {
             error_log('Error in rolesWithPermissionById: ' . $th->getMessage());
             return response()->json([
                 'success' => 0,
                 'error' => 1,
                 'message' => 'Something went wrong',
                 'data' => null
             ], 500);
         }
     }

}
