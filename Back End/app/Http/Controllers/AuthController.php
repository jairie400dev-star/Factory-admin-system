<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse as HttpJsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function adminLogin(Request $request): HttpJsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $token = $this->authService->loginAdmin($request->only(['email', 'password']));

        if (empty($token)) {
            return JsonResponse::error('Invalid credentials or unauthorized.', null, 401);
        }

        return JsonResponse::success($token);
    }

    public function profile(Request $request): HttpJsonResponse
    {
        $user = $request->user();

        return JsonResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function logout(Request $request): HttpJsonResponse
    {
        $deleted = $this->authService->logout($request);

        return JsonResponse::success([
            'message' => $deleted ? 'Logged out successfully.' : 'No token to revoke.',
        ]);
    }

}
