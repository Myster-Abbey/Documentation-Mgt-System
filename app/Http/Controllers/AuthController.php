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
use App\Mail\OtpMail;
use App\Models\Document;
use App\Models\DocumentRequest;

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
                    'token' => null,
                    'download_requests' => null,
                    'all_documents' => null,
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

                // Generate and send OTP without hashing
                $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $user->update([
                    'otp' => $otp, // Store OTP as plain text
                    'otp_expires_at' => now()->addMinutes(10)
                ]);
                Mail::to($user->email)->send(new OtpMail($otp, $user));

                // Fetch download requests for the logged-in user
                $downloadRequests = DocumentRequest::where('user_id', $user->id)->with(['documents', 'users'])->get();

                $allDocuments = null;

                if ($user->role === 'admin') {
                    $allDocuments = Document::all();
                } else {
                    $allDocuments = null;
                }


                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => $user,
                    'download_requests' => count($downloadRequests) > 0 ? $downloadRequests : null,
                    'all_documents' => $allDocuments,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'token' => null,
                    'download_requests' => null,
                    'all_documents' => null,
                ]);
            }
        } catch (\Throwable $th) {
            Log::info("USER-LOGIN-ERROR: " . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sorry, error occurred',
                'token' => null,
                'download_requests' => null,
                'all_documents' => null,
            ]);
        }
    }

    public function verifyAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|ends_with:@gwosevo.com|exists:users,email',
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return General::apiFailureResponse($validator->errors()->first(), 401);
        }

        try {
            $user = User::where('email', $request->email)->first();

            // Check if OTP has expired
            if (!$user->otp_expires_at || now()->gt($user->otp_expires_at)) {
                return General::apiFailureResponse('OTP has expired. Please request a new one.', 401);
            }

            // Verify OTP
            if (!Hash::check($request->otp, $user->otp)) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'Account Verification Failed'
                ]);
                return General::apiFailureResponse('Invalid OTP', 401);
            }

            // Update user verification status
            $user->update([
                'email_verified_at' => now(),
                'otp' => null,
                'otp_expires_at' => null
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Account Verified'
            ]);

            // Generate auth token for automatic login
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Account verified successfully',
                'token' => $token,
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            Log::error('Account Verification Error: ' . $e->getMessage());
            return General::apiFailureResponse('Failed to verify account. Please try again later.', 500);
        }
    }


    public function create(Request $request)
    {
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

            // Generate and send OTP
            $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            // Store OTP in user record
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10)
            ]);

            // Send OTP email
            Mail::to($user->email)->send(new OtpMail($otp, $user));

            // Fetch download requests for the newly created user
            $downloadRequests = DocumentRequest::where('user_id', $user->id)->with(['documents', 'users'])->get();

            // Admin cannot be created via this route, so all documents are not needed.

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully. Please check your email for verification code.',
                'data' => $user,
                'download_requests' => $downloadRequests,
            ], 201);
        } catch (\Throwable $th) {
            Log::info("USER-CREATE-ERROR: " . $th->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error occurred',
                'data' => null,
                'download_requests' => null,
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|ends_with:@gwosevo.com|exists:users,email',
            'otp' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return General::apiFailureResponse($validator->errors()->first(), 401);
        }

        try {
            $user = User::where('email', $request->email)->first();

            // Check if OTP has expired
            if (!$user->otp_expires_at || now()->gt($user->otp_expires_at)) {
                return General::apiFailureResponse('OTP has expired. Please request a new one.', 401);
            }

            // Verify OTP directly without hashing
            if ($request->otp !== $user->otp) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'OTP Verification Failed'
                ]);
                return General::apiFailureResponse('Invalid OTP', 401);
            }

            // Update user verification status
            $user->update([
                'email_verified_at' => now(),
                'otp' => null,
                'otp_expires_at' => null
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'OTP Verified'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            Log::error('OTP Verification Error: ' . $e->getMessage());
            return General::apiFailureResponse('Failed to verify OTP. Please try again later.', 500);
        }
    }
}
