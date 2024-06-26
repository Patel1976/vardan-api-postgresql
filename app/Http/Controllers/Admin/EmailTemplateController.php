<?php

namespace App\Http\Controllers\Admin;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController
{
    //Create Email template
    public function createEmailTemplate(Request $request){
        try {
            $templateName = $request->name;
            $subject = $request->subject;
            $body = $request->body;
            $exsitingTemplate = EmailTemplate::where('name' , $templateName)->first();
            if($exsitingTemplate){
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Email Template Already Exsits',
                    'data' => null
                ], 409);
            }
            $create = EmailTemplate::create([
                'name' => $templateName ,
                'subject' => $subject ,
                'body' => $body
            ]);
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Successfully Created Email Template',
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

    //Edit Email Template
    public function updateEmailTemplate(Request $request){
        try {
            $name = $request->name;
            $subject = $request->subject;
            $body = $request->body;

            $findTemplate = EmailTemplate::where('id' , $request->id)->first();
            if(!$findTemplate){
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Error: Template name is not Found.',
                    'data' => null
                ], 404);
            }
            $exsitingTemplate = EmailTemplate::where('name' , $request->name)->whereNot('id' , $request->id)->first();
            if($exsitingTemplate){
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Email Template Already Exsits',
                    'data' => null
                ], 409);
            }
            $findTemplate->name = $request->name;
            $findTemplate->subject = $request->subject;
            $findTemplate->body  = $request->body;
            $findTemplate->save();
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Email Template is successfully Edited',
                'data' => null
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

    public function getEmailTemplate(Request $request){
        try {
            if(!$request->id){
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Error: Please select any Email Template.',
                    'data' => null
                ], 400);
            }
            $emailTemplate = EmailTemplate::find($request->id);
            if ($emailTemplate) {
                return response()->json([
                    'success' => 1,
                    'error' => 0,
                    'message' => 'Email Template Records',
                    'data' => $emailTemplate
                ], 200);
            } else {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Error',
                    'data' => ''
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Error',
                'data' => $th
            ], 401);
        }
    }

    // Get all template
    public function getAllEmailTemplates(){
        try {
           $findTemplate = EmailTemplate::all();
           return response()->json([
            'success' => 1,
            'error' => 0,
            'message' => 'Email Template List',
            'data' => $findTemplate
        ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Error',
                'data' => $th
            ], 401);
        }
    }

    public function deleteEmailTemplate(Request $request){
        try {
            $emailTemplate = EmailTemplate::where('id' , $request->id)->first();
            if(!$emailTemplate){
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Error: Template name is not Found.',
                    'data' => null
                ], 404);
            }
            $emailTemplate->delete();
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Email Template is successfully Deleted',
                'data' => null
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
}
