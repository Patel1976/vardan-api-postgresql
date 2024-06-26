<?php

namespace App\Http\Controllers\Admin;

use App\Models\SystemModule;
use Illuminate\Http\Request;

class SystemModuleController
{
    public function createModule(Request $request){
       try {
        $module = $request->name;
        if(!$module){
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Error: Module name is required.',
                'data' => null
            ], 400);
        }
            $checkModuleName = SystemModule::where('name' , $module)->first();
            if($checkModuleName){
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Module Already Exsits',
                    'data' => null
                ], 409);
            }
            $action = $request->action;
            $actionJson = json_encode($action);
            $create = SystemModule::create([
                'name' => $request->name,
                'action' => $actionJson,
                'slug' => $request->slug,
            ]);
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Successfully created Module',
                'data' => $create
            ], 201);
       } catch (\Throwable $th) {
        return response()->json([
            'success' => 0,
            'error' => 1,
            'message' => 'Error',
            'data' => $th
        ], 500);
       }
    }

    public function updateModule(Request $request){
        try {
            $name = $request->name;
            $action = $request->action;
            $slug = $request->slug;
    
            if (!$name) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Error: Module name is required.',
                    'data' => null
                ], 400);
            }
    
            $module = SystemModule::where('id', $request->id)->first();

            if (!$module) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Error: Module not found.',
                    'data' => null
                ], 404);
            }
    
            $module->name = $name;
            $module->action = $action;
            $module->slug = $slug;
            $module->save();
    
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Module updated successfully.',
                'data' => $module
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
    
    public function getAllModule(){
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
  
    public function getModuleById (Request $request){
        $id  = $request->id;
        try {
            if(!$id){
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Error: Module ID is required.',
                    'data' => null
                ], 400);
            }
            $moduleName = SystemModule::where('id' , $request->id)->first();
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
    
    public function deleteModule(Request $request){
      try {
          $id = $request->id;
  
          if (!$id) {
              return response()->json([
                  'success' => 0,
                  'error' => 1,
                  'message' => 'Error: Module ID is required.',
                  'data' => null
              ], 400);
          }
  
          $module = SystemModule::find($id);
  
          if (!$module) {
              return response()->json([
                  'success' => 0,
                  'error' => 1,
                  'message' => 'Error: Module not found.',
                  'data' => null
              ], 404); 
          }
  
          $module->delete();
  
          return response()->json([
              'success' => 1,
              'error' => 0,
              'message' => 'Module is successfully deleted',
              'data' => null
          ], 200); 
      } catch (\Throwable $th) {
          return response()->json([
              'success' => 0,
              'error' => 1,
              'message' => 'Error: ' . $th->getMessage(),
              'data' => $th
          ], 500);
      }
  }
  
    
}
