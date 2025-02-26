<?php
namespace App\helper;

class General {
    public static function apiSuccessResponse($message, $statusCode, $data = null)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    public static function apiFailureResponse($message, $statusCode, $data = null)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}

