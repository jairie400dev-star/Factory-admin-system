<?php

namespace App\Helpers;

class JsonResponse
{
    /**
     * Success response
     */
    public static function success($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Error response
     */
    public static function error(string $message = 'Error', $data = null, int $code = 400)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Validation error response
     */
    public static function validation($errors, string $message = 'Validation Error', int $code = 422)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }
}