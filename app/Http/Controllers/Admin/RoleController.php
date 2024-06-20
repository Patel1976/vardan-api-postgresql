<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminUser;
use App\Models\SystemModule;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

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
     public function updateRole(Request $request , $id)
     {
         try {
             $roleName = $request->role;
             $findRole =Role::where('id' , $id)->first();
             if ($findRole) {
                 $findRole->name = $roleName;
                 $findRole->update();
                 return response()->json([
                     'success' => 1,
                     'error' => 0,
                     'message' => $roleName . ' role updated successfully',
                     'data' => null
                 ], 201);                
             }else {
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

    // give role to user
    public function assignRoleToUser(Request $request, AdminUser $id)
    {
        try {
            $roleName = $request->role;
            $role = Role::findByName($request->role);
            if ($role) {
                if (!$id) {
                    return response()->json([
                        'success' => 0,
                        'error' => 1,
                        'message' => 'User not found',
                        'data' => null
                    ], 404);
                }
                $id->assignRole($role);
                $id->role = $roleName;
                $id->save();
                return response()->json([
                    'success' => 1,
                    'error' => 0,
                    'message' => 'Role ' . $roleName . ' assigned successfully',
                    'data' => null
                ], 200);
            } else {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Role ' . $roleName . ' not found',
                    'data' => null
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Failed to assign role: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }


public function assignPermissionsToRole(Request $request, $id)
{
    try {
        // Retrieve permissions from the request
        $permissions = $request->input('permissions', []);
        
        // Find the role by its ID
        $role = Role::find($id);
        if (!$role) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Role not found',
                'data' => null
            ], 404);
        }
        
        $role->syncPermissions([]);
        // Process each permission
        if(!empty($permissions)){
            foreach ($permissions as $permissionId => $moduleArr) {
                $role->permissions()->attach($permissionId);
                $role->permissions()->updateExistingPivot($permissionId, ['module' => json_encode($moduleArr)]);
            }
        }
        return response()->json([
            'success' => 1,
            'error' => 0,
            'message' => 'Permissions assigned to role ' . $role->name . ' successfully',
            'data' => null
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


    // check role
    public function checkUserRole(AdminUser $id, Request $request)
    {
        try {
            $roleName = $request->role;
            if ($id->hasRole($roleName)) {
                return response()->json([
                    'success' => 1,
                    'error' => 0,
                    'message' => 'User is has the role ' . $roleName,
                    'data' => null
                ], 200);                
        } else {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'User is does not has the role ' . $roleName,
                'data' => null
            ], 404);            
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => 0,
            'error' => 1,
            'message' => 'Failed to check user role: ' . $e->getMessage(),
            'data' => null
        ], 500);        
    }
    }

    public function getRoleById($id) {
        try {
            $role = Role::find($id);
            if(!$role) {
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
        } catch(\Exception $e) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => $e->getMessage(),
                'data' => null
            ], 401);
        }
     }
    
     // Remove Permission From A Role using Id
     public function removeRolePermissionById(Request $request, $id)
{
    try {
        // Retrieve permissions and modules from the request
        $permissions = $request->input('permissions', []);
        $modules = $request->input('modules', []);

        // Find the role by its ID
        $role = Role::find($id);
        if (!$role) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Role not found',
                'data' => null
            ], 404);
        }

        // Remove permissions from the role
        if (!empty($permissions)) {
            $role->revokePermissionTo($permissions);
        }

        // Remove modules from the role's permissions
        if (!empty($modules)) {
            foreach ($modules as $module) {
                $role->permissions()->where('module', $module)->detach();
            }
        }

        return response()->json([
            'success' => 1,
            'error' => 0,
            'message' => 'Permissions and modules removed from role successfully',
            'data' => null
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => 0,
            'error' => 1,
            'message' => 'Failed to remove permissions and modules: ' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}

    

    public function getAllRoles()
    {
        try {
            $findAllUsers = Role::where('name', '!=', 'Super Admin')->get();
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => '',
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

    public function permissionList()
    {
        try {
            $findAllUsers = Permission::all();
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => '',
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
    
    public function rolesWithPermission()
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

            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Something went wrong',
                'data' => null
            ], 500); // Changed status code to 500 for server error
        }
    }


public function getPermissionForRole($id)
{
    try {
        $role = Role::find($id);
        if(!$role) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Role not found',
                'data' => null
            ], 404);
        }
        $rolePermissions = DB::table('role_has_permissions')
            ->select('permission_id','module')
            ->where('role_id', $id)
            ->get()
            ->pluck('module', 'permission_id');
        return response()->json([
            'success' => 1,
            'error' => 0,
            'message' => '',
            'data' => $rolePermissions
        ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            'success' => 0,
            'error' => 1,
            'message' => 'Something went wrong',
            'data' => null
        ], 500);
    }
  }
}
