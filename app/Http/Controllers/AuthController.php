<?php

namespace App\Http\Controllers;

use App\Mail\EmailOtpMail;
use App\Models\EmailOtpModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    
    public function generateEmailOtp(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email', 'max:255', 'unique:users,email']
            ]);

            $email = $validated['email'];

            $existingOtp = EmailOtpModel::where('email', $email)
                ->where('expires_at', '>', Carbon::now())
                ->latest()
                ->first();

            if ($existingOtp) {
                $otp = $existingOtp->otp;
            } else {
                EmailOtpModel::where('email', $email)->delete();

                $otp = rand(100000, 999999);
                EmailOtpModel::create([
                    'email'      => $email,
                    'otp'        => $otp,
                    'expires_at' => Carbon::now()->addMinutes(10)
                ]);
            }

            Mail::to($email)->send(new EmailOtpMail($otp));

            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully'
            ], 200);

        }catch (\Exception $e) {
            Log::error(__METHOD__ . ' Error : ' . $e->getMessage());
            
            return response()->json([
                'status'  => false,
                'message' => 'Failed to generate OTP. Please try again.',
            ], 500);
        }
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'otp'      => ['required', 'numeric', 'digits:6']
        ]);

        $otpRecord = EmailOtpModel::where('email', $validated['email'])
            ->where('otp', $validated['otp'])
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired OTP.'
            ], 422);
        }

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => $validated['password'],
            'api_key'           => hash('sha256', Str::uuid()),
            'email_verified_at' => Carbon::now(),
        ]);

        $otpRecord->delete();

        $token = $user->createToken('api-access')->plainTextToken;

        return response()->json([
            'status' => true,
            'data' => [
                'user_id'      => $user->id,
                'api_key'      => $user->api_key,
                'access_token' => $token,
            ]
        ], 201);
    }
}
