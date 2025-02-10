<?php

namespace App\Http\Controllers;

use App\helper\General;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|ends_with:@gwosevo.com',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            return General::apiFailureResponse($message, 401);
        }

        try {
            $user = User::where('email', $request->email)->first();

            // Return early if user doesn't exist
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account cannot be found',
                    'token' => null
                ]);
            }

            $credentials = [
                'email' => $user->email,
                'password' => $request->password
            ];

            // Log login attempt only if user exists
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Login Attempted'
            ]);

            if (auth()->attempt($credentials)) {
                $token = $user->createToken('authToken')->plainTextToken;
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'token' => $token
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'token' => null
                ]);
            }
        } catch (\Throwable $th) {
            Log::info("USER-LOGIN-ERROR: " .  $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sorry, error occurred',
                'token' => null
            ]);
        }
    }

    public function verifyAccount(Request $request)
    {
    }

    public function create(Request $request)
    {
        // return 'hello';
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|ends_with:@gwosevo.com|unique:users,email',
            'phone_number' => 'required|string|unique:users,phone_number',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            return General::apiFailureResponse($message, 401);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => bcrypt($request->password)
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Account Created'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully',
                'data' => $user
            ], 201);

        } catch (\Throwable $th) {
            Log::info("USER-CREATE-ERROR: " . $th->getMessage());
           return General::apiFailureResponse('Error occurred', 500);
        }
    }

    // public function sendOtp(Request $request) {
    //     // OTP sending logic
    //     $otp = rand(100000, 999999);
    //     Mail::to($request->email)->send(new OtpMail($otp));
    //     return response()->json(['message' => 'OTP sent']);
    // }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|ends_with:@gwosevo.com|exists:users,email',
        ]);

        if ($validator->fails()) {
            return General::apiFailureResponse($validator->errors()->first(), 401);
        }

        try {
            $user = User::where('email', $request->email)->first();
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store OTP in user record
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10) // OTP valid for 10 minutes
            ]);

            // Send OTP email
            Mail::send('emails.otp', [
                'user' => $user,
                'otp' => $otp
            ], function($message) use ($user) {
                $message->to($user->email)
                        ->subject('Your OTP Code');
            });

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'OTP Sent'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP has been sent to your email'
            ]);

        } catch (\Throwable $th) {
            Log::info("OTP-SEND-ERROR: " . $th->getMessage());
            return General::apiFailureResponse('Error sending OTP', 500);
        }
    }
}
