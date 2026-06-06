<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $headers = [
            'Access-Control-Allow-Origin' => env('CORS_ALLOWED_ORIGINS', '*'),
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => $request->header('Access-Control-Request-Headers', 'Content-Type, Authorization'),
            'Access-Control-Expose-Headers' => 'Authorization',
            'Access-Control-Allow-Credentials' => 'true',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json([], 204, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
