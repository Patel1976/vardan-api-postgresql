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

    //Login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            $user = \App\Models\AdminUser::where('email', $credentials['email'])->first();
            if (!$user) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Validation failed',
                    'data' => [
                        'errors' => [
                            'email' => ['Email is incorrect.']
                        ]
                    ]
                ], 401);
            }
            if (!\Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Validation failed',
                    'data' => [
                        'errors' => [
                            'password' => ['Password is incorrect.']
                        ]
                    ]
                ], 401);
            }
            $token = JWTAuth::fromUser($user);
            if (!$token) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'Failed to generate token',
                    'data' => null
                ], 500);
            }
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Login Success',
                'data' => [
                    'token' => $token,
                    'userData' => $user,
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json("Login Failed " . $th->getMessage(), 500);
        }
    }

    //Logout
    public function logout()
    {
        $response = new Response("Logout Successful", 200);
        $response->cookie(Cookie::forget('jwt_token'));
        return $response;
    }

    //Verify JWT Token
    public function verifyJWT()
    {
        return response()->json('Jwt verified', 200);
    }

    //Refresh JWT Token
    public function refreshJWT(Request $request)
    {
        $token = $request->token;
        try {
            $bearerToken = $request->header('Authorization');
            $token = substr($bearerToken, 7);
            $userId = JWTAuth::setToken($token)->toUser()->id;
            if ($userId) {
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
    
    // ---> Forget Controller
    //Send Forget password
    public function sendForgetPasswordEmail(Request $request)
    {
        $email = $request->email;
        $token = uniqid();
        $domain = env('FORNTEND_URL') . 'reset-password/' . $token;
        $findEmail = AdminUser::where('email', $email)->first();
        try {
            if ($findEmail) {
                $findResetToken = $findEmail->token;
                $tokenCreatedAt = Carbon::parse($findEmail->token_created_at);
                $expiryTime = Carbon::now()->subMinutes(5);
                if ($tokenCreatedAt < $expiryTime) {
                    $findEmail->token = null;
                    $findEmail->token_created_at = null;
                    $findEmail->update();
                    return response()->json([
                        'message' => 'Please try again after some time.'
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
                    Mail::send([], [], function ($message) use ($emailTemplate, $request, $emailBody) {
                        $message->to($request->email)
                        ->subject($emailTemplate->subject)
                        ->html($emailBody);
                    });
                    return response()->json([
                        'message' => 'Forget password mail is sent'
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Reset email already sent. Please try again later.'
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'User not found or unauthorized user'
                ], 401);
            }
        } catch (\Throwable $th) {
            throw $th;
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

    // Used for check the link is valid or not
    public function verifyForgetToken($token)
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

    // ----> Reset Controller
    public function resetPassword(Request $request)
    {
        $currentPassword = $request->currentPassword;
        $newPassword = $request->newPassword;
        $bearerToken = $request->header('Authorization');
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
                        ], 400);
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

}
