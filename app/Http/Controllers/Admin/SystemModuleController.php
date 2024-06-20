<?php

namespace App\Http\Controllers\Admin;

use App\Models\SystemModule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\AdminUser;
use Tymon\JWTAuth\Facades\JWTAuth;

class SystemModuleController
{
    public function checkModule(Request $request)
    {
        $id = $request->id;
        try {
            if (!$id) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Error: Module ID is required.',
                    'data' => null
                ], 400);
            }
            $moduleName = SystemModule::where('id', $request->id)->first();
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'System Module Records',
                'data' => $moduleName
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Error',
                'data' => $th
            ], 500);
        }
    }
    public function getAllModule()
    {
        try {

            $findModule = SystemModule::all();
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Module List',
                'data' => $findModule
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Error',
                'data' => $th
            ], 500);
        }
    }

    public function getAllAssignModule(Request $request)
    {
        try {
            $moduleQuery = SystemModule::where('status', true);
            if ($request->is_permissible == 'true') {
                $moduleQuery->where('is_permissible', true);
            } else {
                $moduleQuery->with('subModules')->whereNull('parent_module_id');
            }
            $moduleQuery->orderBy('display_order', 'ASC');

            $findModule = $moduleQuery->get();
        
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Module List',
                'data' => $findModule
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Error',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function fetchUsersModule(Request $request)
    {
        try {
            $bearerToken = $request->header('Authorization');
            $token = substr($bearerToken, 7);
            $user = JWTAuth::setToken($token)->toUser();

            $roleIds = $user->roles->pluck('id')->toArray();

            $userModules = DB::table('role_has_permissions')
                ->whereIn('role_id', $roleIds)
                ->where('permission_id', 1)
                ->first('module');

            $finalMenuData = [];
            $slugs = [];
            if ($userModules && $userModules->module) {
                $slugs = json_decode($userModules->module);
            }

            if (!in_array('dashboard', $slugs)) {
                $slugs[] = 'dashboard';
            }

            $moduleQuery = SystemModule::where('status', true);
            if(in_array(1, $roleIds)){
                $moduleQuery->with(['subModules']);
                $moduleQuery->whereNull('parent_module_id');
            } else {
                $moduleQuery->with([
                    'subModules' => function ($query) use ($slugs) {
                        $query->whereIn('slug', $slugs);
                    }
                ]);
                $moduleQuery->whereNull('parent_module_id');
                $moduleQuery->where(function ($query) use ($slugs) {
                    $query->whereIn('slug', $slugs)
                        ->orWhereNull('slug');
                });
            }
            
            $moduleQuery->orderBy('display_order', 'ASC');
            $moduleData = $moduleQuery->get();
            if ($moduleData) {
                $moduleData = $moduleData->toArray();
                if (!empty($moduleData)) {
                    foreach ($moduleData as $data) {
                        if (
                            (!empty($data['sub_modules'])) ||
                            (empty($data['sub_modules']) && !empty($data['slug']))
                        ) {
                            $finalMenuData[] = $data;
                        }

                    }
                }
            }
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Modules and Submodules accessible by the user',
                'data' => $finalMenuData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Error',
                'data' => $th->getMessage()
            ], 500);
        }
    }
}
