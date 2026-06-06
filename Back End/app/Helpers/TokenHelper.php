<?php

namespace App\Helpers;

use Laravel\Sanctum\PersonalAccessToken;

class TokenHelper
{
    public static function format(string $plainTextToken, PersonalAccessToken $token): array
    {
        return [
            'access_token' => $plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->expires_at?->toDateTimeString(),
        ];
    }
}
