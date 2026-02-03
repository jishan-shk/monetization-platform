<?php

namespace App\Http\Controllers;

use App\Mail\EmailOtpMail;
use App\Models\EmailOtpModel;
use App\Models\SubscriptionTiersModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    
    public function generateEmailOtp(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email', 'max:255']
            ]);

            $email = $validated['email'];

            if(env('APP_ENV') == 'prod'){
                $key = $email;
                $maxAttempts = env('OTP_LIMIT_ATTEMPTS') ?? 3;
                $decaySeconds = env('OTP_LIMIT_DELAY_SEC') ?? 3600;

                if ($request->mobile != env('DEFAULT_NUMBER') && RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                    Log::critical("UsersController Request Blocked By Rate limiter", ['$request' => $request->all()]);
                    return Response::json([
                        'status'        => false,
                        'message'       => 'Oops! You have reached maximum limit, please try again after an hour.',
                    ], TOO_MANY_ATTEMPT_CODE);
                }

                RateLimiter::hit($key, $decaySeconds);
            }

            $userExists = User::where('email', $email)->exists();

            $existingOtp = EmailOtpModel::where('email', $email)
                ->where('expires_at', '>', Carbon::now())
                ->latest()
                ->first();

            if ($existingOtp) {
                $otp = $existingOtp->otp;
            } else {
                EmailOtpModel::where('email', $email)->delete();

                if(env('APP_ENV') == 'prod'){
                    $otp = rand(100000, 999999);
                } else {
                    $otp = 123456;
                }
                
                EmailOtpModel::create([
                    'email'      => $email,
                    'otp'        => $otp,
                    'expires_at' => Carbon::now()->addMinutes(10)
                ]);
            }

            Mail::to($email)->send(new EmailOtpMail($otp));

            return response()->json([
                'status'        => true,
                'message'       => 'OTP sent successfully',
                'user_exists'   => $userExists
            ], SUCCESS_REQUEST_CODE);

        } catch (\Illuminate\Validation\ValidationException $error) {
            Log::error(__METHOD__ . ' Validation Error : ' . $error->getMessage());

            return response()->json([
                'status'  => false,
                'message' => VALIDATION_ERROR_MSG,
                'errors'  => $error->errors(),
            ], BAD_REQUEST_CODE);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ' Error : ' . $e->getMessage());
            
            return response()->json([
                'status'  => false,
                'message' => 'Failed to generate OTP. Please try again.',
            ], 500);
        }
    }

    public function register(Request $request): JsonResponse
    {
        try{
            $validated = $request->validate([
                'subscription_tier'  => ['required', 'string', 'exists:subscription_tiers,name'],
                'name'               => ['required', 'string', 'max:255'],
                'email'              => ['required', 'email', 'max:255', 'unique:users,email'],
                'password'           => ['required', 'string', 'min:8', 'confirmed'],
                'otp'                => ['required', 'numeric', 'digits:6']
            ]);

            $otpRecord = EmailOtpModel::where('email', $validated['email'])
                ->where('otp', $validated['otp'])
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid or expired OTP.'
                ], UNPROCESSABLE_CONTENT_CODE);
            }

            if (User::where('email', $validated['email'])->exists()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This email is already registered. Please log in.'
                ], UNPROCESSABLE_CONTENT_CODE);
            }

            $subscription_tier_id = SubscriptionTiersModel::where('name', $validated['subscription_tier'])->value('id');
            $user = User::create([
                'name'                  => $validated['name'],
                'email'                 => $validated['email'],
                'password'              => $validated['password'],
                'api_key'               => hash('sha256', Str::uuid()),
                'subscription_tier_id'  => $subscription_tier_id,
                'email_verified_at'     => Carbon::now(),
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
            ], SUCCESS_REQUEST_CODE);
        } catch (\Illuminate\Validation\ValidationException $error) {
            Log::error(__METHOD__ . ' Validation Error : ' . $error->getMessage());

            return response()->json([
                'status'  => false,
                'message' => VALIDATION_ERROR_MSG,
                'errors'  => $error->errors(),
            ], BAD_REQUEST_CODE);
        }catch (\Exception $e) {
            Log::error(__METHOD__ . ' Error : ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Registration failed. Please try again.',
            ], INTERNAL_SERVER_ERROR_CODE);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try{
            $validated = $request->validate([
                'email' => ['required', 'email', 'exists:users,email'],
                'otp'   => ['required', 'numeric', 'digits:6']
            ]);

            $otpRecord = EmailOtpModel::where('email', $validated['email'])
                ->where('otp', $validated['otp'])
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid or expired OTP.'
                ], UNPROCESSABLE_CONTENT_CODE);
            }

            $user = User::where('email', $validated['email'])->first();

            $otpRecord->delete();

            $token = $user->createToken('api-access')->plainTextToken;

            return response()->json([
                'status' => true,
                'data'   => [
                    'user_id'      => $user->id,
                    'name'         => $user->name,
                    'access_token' => $token,
                ]
            ], SUCCESS_REQUEST_CODE);
        } catch (\Illuminate\Validation\ValidationException $error) {
            Log::error(__METHOD__ . ' Validation Error : ' . $error->getMessage());

            return response()->json([
                'status'  => false,
                'message' => VALIDATION_ERROR_MSG,
                'errors'  => $error->errors(),
            ], BAD_REQUEST_CODE);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ' Error : ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Login failed. Please try again.',
            ], INTERNAL_SERVER_ERROR_CODE);
        }
    }
}
