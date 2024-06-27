<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SmsTemplateController;
use App\Http\Controllers\Admin\SystemModuleController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\RoleOrPermissionMiddleware;
use App\Http\Controllers\StaffUserController;


use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    //---> Authentication Routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    //---> Forget Password Routes
    Route::post('/auth/send-forget-password-email', [AuthController::class, 'sendForgetPasswordEmail']);
    Route::post('/auth/reset-password/{token}', [AuthController::class, 'resetPasswordWithToken']);
    Route::post('/auth/verify-forget-token/{token}', [AuthController::class, 'verifyForgetToken']);
});

// without spatie middleware
Route::middleware([JwtMiddleware::class])->prefix('admin')->group(function () {
  //---> Authentication Routes
  Route::post('/auth/logout', [AuthController::class, 'logout']);
  Route::post('/auth/verifyJWT', [AuthController::class, 'verifyJWT']);
  Route::post('/auth/refreshJWT', [AuthController::class, 'refreshJWT']);
  //---> Reset Password Api Routes
  Route::post('/user-profile', [AdminUserController::class, 'userProfile']);
  Route::put('/edit-profile/{id}', [AdminUserController::class, 'editProfile']);
  Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
  Route::post('/get-all-user-module', [SystemModuleController::class, 'fetchUsersModule']);
  Route::post('/get-all-assign-module', [SystemModuleController::class, 'getAllAssignModule']);
});

Route::prefix('staff-users')->group(function(){
  // ---> Staff User Routes
  Route::post('create-staff-user',[StaffUserController::class,'createStaffUser']);
  Route::put('update-staff-user/{id}',[StaffUserController::class,'updateStaffUser']);
  Route::get('get-all-staff-users',[StaffUserController::class,'getAllStaffUsers']);
  Route::get('get-staff-user/{id}',[StaffUserController::class,'getStaffUserById']); 
  Route::delete('delete-staff-user/{id}',[StaffUserController::class,'deleteStaffUser']);
  Route::post('time-log/{id}',[StaffUserController::class,'StaffTimelog']);
  Route::get('get-time-log/{id}',[StaffUserController::class,'getStaffTimelog']);
  Route::get('get-time-log-by-date/{id}',[StaffUserController::class,'getStaffTimelogByDate']);
  Route::post('get-time-log-by-range/{id}',[StaffUserController::class,'getStaffTimelogByRange']);
  Route::post('emergency-image-log/{id}',[StaffUserController::class,'imagelog']);
  Route::get('get-image-log/{id}',[StaffUserController::class,'getStaffImageLog']);  //not working
});


Route::middleware([JwtMiddleware::class, RoleOrPermissionMiddleware::class])->prefix('admin')->group(function () {
    // --> Role route
    Route::post('/create-role', [RoleController::class, 'createRole']);
    Route::put('/update-role/{id}', [RoleController::class, 'updateRole']);
    Route::post('/get-all-roles', [RoleController::class, 'getAllRoles']);
    Route::post('/get-role-by-id/{id}', [RoleController::class, 'getRoleById']);
    Route::delete('/delete-role/{id}', [RoleController::class, 'deleteRole']);
    Route::post('/get-role-with-permission/{id}', [RoleController::class, 'getRoleWithPermissionById']);
    Route::post('/assign-permissions-to-role/{id}', [RoleController::class, 'assignPermissionsToRoleById']);
    Route::post('/get-all-roles-with-permission', [RoleController::class, 'getAllRolesWithPermission']);

    //---> User Routes
    Route::post('/create-user', [AdminUserController::class, 'createUser']);
    Route::put('/update-user/{id}', [AdminUserController::class, 'updateUser']);
    Route::post('/get-all-users', [AdminUserController::class, 'getAllUsers']);
    Route::post('/get-user-by-id/{id}', [AdminUserController::class, 'getUserById']);
    Route::delete('/delete-user/{id}', [AdminUserController::class, 'deleteUser']);
    Route::put('/user/change-password/{id}', [AdminUserController::class, 'changeUserPassword']); 
    

    //--> Email Template
    Route::post('/create-email-template', [EmailTemplateController::class, 'createEmailTemplate']);
    Route::put('/update-email-template', [EmailTemplateController::class, 'updateEmailTemplate']);
    Route::post('/get-email-template/{id}', [EmailTemplateController::class, 'getEmailTemplate']);
    Route::post('/get-all-email-templates', [EmailTemplateController::class, 'getAllEmailTemplates']);
    Route::delete('/delete-email-template', [EmailTemplateController::class, 'deleteEmailTemplate']);

    //--> SMS Template
    Route::post('/create-sms-template', [SmsTemplateController::class, 'createSmsTemplate']);
    Route::put('/update-sms-template', [SmsTemplateController::class, 'updateSmsTemplate']);
    Route::post('/get-all-sms-template', [SmsTemplateController::class, 'getAllSmsTemplate']);
    Route::post('/get-sms-template/{id}', [SmsTemplateController::class, 'getSmsTemplateById']);
    Route::delete('/delete-sms-template', [SmsTemplateController::class, 'deleteSmsTemplate']);

    // System Module
    Route::post('/create-module', [SystemModuleController::class, 'createModule']);
    Route::put('/update-module', [SystemModuleController::class, 'updateModule']);
    Route::post('/get-module-by-id', [SystemModuleController::class, 'getModuleById']);
    Route::post('/get-all-module', [SystemModuleController::class, 'getAllModule']);
    Route::delete('/delete-module', [SystemModuleController::class, 'deleteModule']);
});


