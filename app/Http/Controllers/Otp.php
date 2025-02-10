<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Otp extends Controller
{
    //
    public function sendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email|ends_with:@gwosevo.com'
        ]);

        $otp = rand(100000, 999999);
        // Send OTP email logic here

        return response()->json(['message' => 'OTP sent successfully']);
    }
}
