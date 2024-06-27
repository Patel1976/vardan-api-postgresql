<?php

namespace App\Http\Controllers\Admin;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController
{
    public function createSmsTemplate(Request $request){
        try {
            $smsTemplate = SmsTemplate::where('name' , $request->name)->first();
            if($smsTemplate){
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Template Already Exsits',
                    'data' => null
                ], 409);
            }
            $create = SmsTemplate::create([
                'name' => $request->name ,
                'body' => $request->body
            ]);
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Successfully created sms template',
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

    //Edit sms template
    public function updateSmsTemplate(Request $request){
      try {
          $smsName = $request->name;
          $smsBody = $request->body;
          
          if (!$smsName) {
              return response()->json([
                  'success' => 0,
                  'error' => 1,
                  'message' => 'Error: sms Template name is required.',
                  'data' => null
              ], 400);
          }
          
          $smsTemplate = SmsTemplate::find($request->id);
          
          if (!$smsTemplate) {
              return response()->json([
                  'success' => 0,
                  'error' => 1,
                  'message' => 'Sms Template not found.',
                  'data' => null
              ], 404);
          }
          
          $smsTemplate->name = $smsName;
          $smsTemplate->body = $smsBody;
          $smsTemplate->save();
          
          return response()->json([
              'success' => 1,
              'error' => 0,
              'message' => 'Sms Template is successfully edited.',
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
  
    // Get all SMS template
    public function getAllSmsTemplate(){
        try {
           $findTemplate = SmsTemplate::all();
           return response()->json([
            'success' => 1,
            'error' => 0,
            'message' => 'SMS Template List',
            'data' => $findTemplate
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
    //Get SMS Template
    public function getSmsTemplateById($id){
        try {
            if (!$id) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Error: SMS Template id is required.',
                    'data' => null
                ], 400);
            }
            
            $smsTemplate = SmsTemplate::find($id);  
            
            if (!$smsTemplate) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'SMS Template not found.',
                    'data' => null
                ], 404);
            }
    
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'SMS Template Record',
                'data' => $smsTemplate
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
    
    //Delete SMS Template
    public function deleteSmsTemplate(Request $request) {
      try {
          $id = $request->id;
          
          if (!$id) {
              return response()->json([
                  'success' => 0,
                  'error' => 1,
                  'message' => 'Error: SMS Template ID is required.',
                  'data' => null
              ], 400);
          }
          
          $smsTemplate = SmsTemplate::find($id);
          
          if (!$smsTemplate) {
              return response()->json([
                  'success' => 0,
                  'error' => 1,
                  'message' => 'Error: SMS Template not found.',
                  'data' => null
              ], 404);
          }
          
          $smsTemplate->delete();
          
          return response()->json([
              'success' => 1,
              'error' => 0,
              'message' => 'SMS Template is successfully deleted',
              'data' => null
          ], 200);
          
      } catch (\Throwable $th) {
          // Log the exception for debugging purpose        
          return response()->json([
              'success' => 0,
              'error' => 1,
              'message' => 'Error: ' . $th->getMessage(),
              'data' => $th // Provide the full exception details for debugging
          ], 500);
      }
  }
  
  
}
