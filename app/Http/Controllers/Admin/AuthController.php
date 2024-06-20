<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Auth;

class AuthController
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Incorrect email or password',
                    'data' => null
                ], 422);
            }
            $user = Auth::user();
            $token = JWTAuth::fromUser($user);
            if (!$token) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Failed to generate token',
                    'data' => null
                ], 500);
            }
            $response = response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Login Success',
                'data' => [
                    'token' => $token,
                    'userData' => $user,
                ]
            ], 200);
            return $response;
        } catch (\Throwable $th) {
            return response()->json("Login Failed " . $th->getMessage(), 500);
        }
    }
    public function logout()
    {
        $response = new Response("Logout Successful", 200);
        $response->cookie(Cookie::forget('jwt_token'));
        return $response;
    }
    public function verifyJwt()
    {
        return response()->json('Jwt verified', 200);
    }
    public function refreshJwt(Request $request)
{
    $token = $request->token;
    try {
        $bearerToken =  $request->header('Authorization');
        $token = substr($bearerToken, 7);
        $userId = JWTAuth::setToken($token)->toUser()->id;
        if($userId){
            $newToken = JWTAuth::refresh($token);
        }
        return response()->json([
            'success' => true,
            'message' => 'Successfully generated new token',
            'data' => [
                'token' => $newToken,
            ]
        ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to generate new token',
            'message' => $th->getMessage(),
            'data' => null
        ], 500);
    }
}
    // ----> Reset Controller
    public function ChangePassword(Request $request)
    {
        $currentPassword = $request->currentPassword;
        $newPassword = $request->newPassword;
        $bearerToken =  $request->header('Authorization');
        $token = substr($bearerToken, 7);
        $userId = JWTAuth::setToken($token)->toUser()->id;
        $user = AdminUser::find($userId);
        try {
            $findUser = AdminUser::where('email', $user->email)->first();
            if ($findUser) {
                if (Hash::check($currentPassword, $findUser->password)) {
                    if (!$currentPassword === !$newPassword) {
                        $hashPassword = bcrypt($newPassword);
                        $findUser->password = $hashPassword;
                        $findUser->update();
                        return response()->json([
                            'success' => 1,
                            'error' => 0,
                            'message' => 'Password changed successfully',
                            'data' => null
                        ], 200);
                    } else {
                        return response()->json([
                            'success' => 0,
                            'error' => 1,
                            'message' => 'New password should not be same with current password',
                            'data' => null
                        ], 409);
                    }
                } else {
                    return response()->json([
                        'success' => 0,
                        'error' => 1,
                        'message' => 'Current Password is incorrect',
                        'data' => null
                    ], 401);
                }
            } else {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'User not found',
                    'data' => null
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Something went wrong',
                'data' => null
            ], 500);
        }
    }

    // ---> Forget Controller
    public function sendForgetPasswordEmail(Request $request)
    {
        $email = $request->email;
        $token = uniqid();
        $domain = env('FORNTEND_URL') .'forget-password/' . $token;
        $findEmail = AdminUser::where('email', $email)->first();
        try {
            if ($findEmail) {
                $findResetToken = $findEmail->token;
                $tokenCreatedAt = Carbon::parse($findEmail->token_created_at);
                $expiryTime = Carbon::now()->subMinutes(5);
                if($tokenCreatedAt < $expiryTime){
                        $findEmail->token = null;
                        $findEmail->token_created_at = null;
                        $findEmail->update();
                        return response()->json([
                            'success' => 1,
                            'error' => 0,
                            'message' => 'Please try again after some time.',
                            'data' => null
                        ], 200);
                }
                if (!$findResetToken) {
                    $findEmail->token = $token;
                    $findEmail->token_created_at = now();
                    $findEmail->save();
                    $userName = $findEmail->name;
                    $emailTemplate = EmailTemplate::where('name', 'FORGET_PASSWORD')->first();
                    $replacements = [
                        '[NAME]' => $userName,
                        '[PASSWORD_RESET_LINK]' => $domain,
                        '[DOMAIN]' => "<a href='$domain' style='color: blue;'>$domain</a>",
                    ];
                    $emailBody = str_replace(array_keys($replacements), array_values($replacements), $emailTemplate->body);
                    Mail::send([], [], function ($message) use ($emailTemplate, $request , $emailBody) {
                        $message->to($request->email)
                                ->subject($emailTemplate->subject)
                                ->html($emailBody);
                    });
                    return response()->json([
                        'success' => 1,
                        'error' => 0,
                        'message' => 'Forget password mail is sent',
                        'data' => null
                    ], 201);
                } else {
                    return response()->json([
                        'success' => 1,
                        'error' => 0,
                        'message' => 'Reset email already sent. Please try again later.',
                        'data' => null
                    ], 200);
                }
            } else {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'User not found or unauthorized user',
                    'data' => null
                ], 401);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    // Used for check the link is valid or not
    public function checkForgetToken($token)
    {
        try {
            $findToken = AdminUser::where('token', $token)->first();
            if (!$findToken) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Invalid Token',
                    'data' => null
                ], 404);
            }
            $tokenCreatedAt = Carbon::parse($findToken->token_created_at);
            $expiryTime = Carbon::now()->subMinutes(5);
            if ($tokenCreatedAt > $expiryTime) {
                return response()->json([
                    'success' => 1,
                    'error' => 0,
                    'message' => 'Valid Token',
                    'data' => null
                ], 201);
            } else {
                $findToken->token = null;
                $findToken->token_created_at = null;
                $findToken->update();
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Token Expired',
                    'data' => null
                ], 403);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Invalid Token',
                'data' => null
            ], 404);
        }
    }

    // Password Change using Mailed Link
    public function resetPasswordWithToken(Request $request, $token)
    {
        $password = $request->password;
        try {
            $findToken = AdminUser::where('token', $token)->first();
            if ($findToken) {
                $findUser = AdminUser::where('email', $findToken->email)->first();
                if ($findUser) {
                    if (!$password) {
                        return response()->json([
                            'success' => 0,
                            'error' => 1,
                            'message' => 'Password cannot be blank',
                            'data' => null
                        ], 404);
                    }
                    $tokenCreatedAt = Carbon::parse($findUser->token_created_at);
                    $expiryTime = Carbon::now()->subMinutes(15);
                    if ($tokenCreatedAt > $expiryTime) {
                        $findUser->password = bcrypt($password);
                        $findUser->token = null;
                        $findUser->token_created_at = null;
                        $findUser->update();
                        return response()->json([
                            'success' => 1,
                            'error' => 0,
                            'message' => 'Password changed successfully',
                            'data' => null
                        ], 200);
                    }
                    $findUser->token = null;
                    $findUser->token_created_at = null;
                    $findUser->update();
                    return response()->json([
                        'success' => 0,
                        'error' => 1,
                        'message' => 'Token Expired',
                        'data' => null
                    ], 403);
                } else {
                    return response()->json([
                        'success' => 0,
                        'error' => 1,
                        'message' => 'User not found',
                        'data' => null
                    ], 404);
                }
            } else {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Invalid token',
                    'data' => null
                ], 400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Invalid Token',
                'data' => null
            ], 404);
        }
    }
}
