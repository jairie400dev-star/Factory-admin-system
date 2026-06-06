<?php

namespace App\Services;

use App\Helpers\TokenHelper;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function loginAdmin(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->isAdministrator()) {
            return [];
        }

        $token = $user->createToken('admin-access-token', ['*'], now()->addHours(3));

        return TokenHelper::format($token->plainTextToken, $token->accessToken);
    }

    public function logout(Request $request): bool
    {
        $token = $request->user()?->currentAccessToken();

        if (! $token) {
            return false;
        }

        $token->delete();

        return true;
    }
}
