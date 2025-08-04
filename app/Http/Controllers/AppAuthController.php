<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\StaffUser;
use App\Models\DeviceSession;
use App\Services\TwilioService;

class AppAuthController
{
    public function checkDeviceLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:15',
            'device_id' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }
        $phone = $request->input('phone');
        $currentDeviceId = $request->input('device_id');
        $deviceSession = DeviceSession::where('phone', $phone)->first();
        if ($deviceSession) {
            if ($deviceSession->device_id !== $currentDeviceId) {
                return response()->json([
                    'status' => 'conflict',
                    'message' => 'This phone number is already logged in on another device (' . ($deviceSession->device_name ?? 'Unknown Device') . '). Do you want to log out that device and log in here?'
                ]);
            }
            return response()->json(['status' => 'ok']);
        }
        return response()->json(['status' => 'ok']);
    }

    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/',
            'device_id' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:255',
            'force_logout' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $phone = $request->input('phone');
        $currentDeviceId = $request->input('device_id');
        $currentDeviceName = $request->input('device_name');
        $forceLogout = $request->input('force_logout', false);

        $otp = rand(1000, 9999);
        $otp = '1234';
        $staff = StaffUser::where('phone', $request->phone)->first();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $staff->otp = $otp;
        $staff->save();

        $deviceSession = DeviceSession::firstOrNew(['phone' => $phone]);

        if (!$deviceSession->exists) {
            $deviceSession->device_id = $currentDeviceId;
            $deviceSession->device_name = $currentDeviceName;
            $deviceSession->last_login_at = now();
            $deviceSession->save();
        } elseif ($deviceSession->device_id !== $currentDeviceId) {
            if (!$forceLogout) {
                return response()->json([
                    'success' => false,
                    'status' => 'conflict',
                    'message' => 'This phone number is already logged in on another device (' . ($deviceSession->device_name ?? 'Unknown Device') . '). Do you want to log out that device and log in here?',
                ], 409);
            }
            $deviceSession->device_id = $currentDeviceId;
            $deviceSession->device_name = $currentDeviceName;
            $deviceSession->last_login_at = now();
            $deviceSession->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
        ]);

        // try {
        //     $twilio = new TwilioService();
        //     $formattedPhone = '+91' . ltrim($request->phone, '0');
        //     $twilio->sendSms($formattedPhone, "Your OTP is: {$otp}");
        // } catch (\Exception $e) {
        //     \Log::error("Failed to send OTP via Twilio: " . $e->getMessage());
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Failed to send OTP.',
        //     ], 500);
        // }

        // return response()->json([
        //     'success' => true,
        //     'message' => 'OTP sent successfully.',
        // ]);
    }

    public function loginWithOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/',
            'otp'   => 'required|digits:4',
            'device_id' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Validation failed',
                'data' => [
                    'errors' => $validator->errors()
                ]
            ], 422);
        }

        $phone = $request->input('phone');
        $otp = $request->input('otp');
        $currentDeviceId = $request->input('device_id');
        $currentDeviceName = $request->input('device_name');

        $staff = StaffUser::where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->first();

        if (!$staff) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Invalid OTP or phone number',
                'data' => null,
            ], 401);
        }

        $staff->otp = null;
        $token = auth('staff')->login($staff);
        $staff->update(['jwt_token' => $token]);
        return response()->json([
            'success' => 1,
            'data' => [
                'token' => $token,
                'userData' => $staff,
            ]
        ]);
        $staff->save();

        $deviceSession = DeviceSession::updateOrCreate(
            ['phone' => $phone],
            [
                'device_id' => $currentDeviceId,
                'device_name' => $currentDeviceName,
                'last_login_at' => now(),
            ]
        );

        $filteredStaffData = [
            'id' => $staff->id,
            'uuid' => $staff->uuid,
            'name' => $staff->name,
            'email' => $staff->email,
            'phone' => $staff->phone,
            'address' => $staff->address,
            'department' => $staff->department,
            'image' => $staff->image,
        ];
        return response()->json([
            'success' => 1,
            'error' => 0,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'userData' => $filteredStaffData,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            $bearerToken = $request->header('Authorization');
            $token = substr($bearerToken, 7);
            $staff = JWTAuth::setToken($token)->authenticate();
            if ($staff) {
                $staff->jwt_token = null;
                $staff->save();
            }
            JWTAuth::setToken($token)->invalidate();
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log out. Try again.',
            ], 500);
        }
    }

    public function verifyJWT()
    {
        return response()->json('JWT verified', 200);
    }

    // public function refreshJWT(Request $request)
    // {
    //     try {
    //         $bearerToken = $request->header('Authorization');
    //         $token = $bearerToken ? substr($bearerToken, 7) : null;
    //         if (!$token) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Token not provided',
    //                 'data' => null
    //             ], 400);
    //         }
    //         $newToken = JWTAuth::setToken($token)->refresh();
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Token refreshed successfully',
    //             'data' => [
    //                 'token' => $newToken,
    //             ]
    //         ]);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'success' => false,
    //             'error' => 'Could not refresh token',
    //             'message' => $th->getMessage(),
    //             'data' => null
    //         ], 500);
    //     }
    // }

    public function refreshJWT(Request $request)
    {
        try {
            $bearerToken = $request->header('Authorization');
            $token = $bearerToken ? substr($bearerToken, 7) : null;
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not provided',
                    'data' => null
                ], 400);
            }
            $deviceId = $request->input('device_id');
            $deviceName = $request->input('device_name');
            if (!$deviceId || !$deviceName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device information required',
                    'data' => null
                ], 400);
            }
            $staff = auth('staff')->setToken($token)->user();
            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                    'data' => null
                ], 401);
            }
            $deviceSession = DeviceSession::where('phone', $staff->phone)->first();
            if (!$deviceSession || $deviceSession->device_id !== $deviceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session invalid. You are logged in on another device.',
                    'data' => null
                ], 401);
            }
            $newToken = auth('staff')->setToken($token)->refresh();
            $deviceSession->device_name = $deviceName;
            $deviceSession->last_login_at = now();
            $deviceSession->save();
            $staff->jwt_token = $newToken;
            $staff->save();
            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $newToken,
                    'userData' => $staff
                ]
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired. Please log in again.',
                'data' => null
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'error' => 'Could not refresh token',
                'message' => $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getLatestToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uuid' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        $staff = StaffUser::where('uuid', $request->uuid)->first();
        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }
        if (empty($staff->jwt_token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token not available'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'token' => $staff->jwt_token
        ]);
    }
}