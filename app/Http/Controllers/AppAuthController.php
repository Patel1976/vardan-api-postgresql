<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\StaffUser;
use App\Services\TwilioService;

class AppAuthController
{
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $otp = rand(1000, 9999);
        $staff = StaffUser::firstOrCreate(
            ['phone' => $request->phone],
            ['otp' => $otp, 'status' => 1]
        );

        $staff->otp = $otp;
        $staff->save();

        try {
            $twilio = new TwilioService();
            $formattedPhone = '+91' . ltrim($request->phone, '0');
            $twilio->sendSms($formattedPhone, "Your OTP is: {$otp}");
        } catch (\Exception $e) {
            \Log::error("Failed to send OTP via Twilio: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
        ]);
    }

    public function loginWithOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/',
            'otp'   => 'required|digits:4',
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
        $staff->save();

        $token = JWTAuth::fromUser($staff);
        if (!$token) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Failed to generate token',
                'data' => null,
            ], 500);
        }
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
            $newToken = JWTAuth::setToken($token)->refresh();
            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $newToken,
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'error' => 'Could not refresh token',
                'message' => $th->getMessage(),
                'data' => null
            ], 500);
        }
    }
}