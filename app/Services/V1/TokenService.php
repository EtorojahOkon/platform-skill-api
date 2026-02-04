<?php

namespace App\Services\V1;

use App\Models\User;
use Illuminate\Support\Carbon;

class TokenService
{
    public function generateSanctumToken(User $user): string
    {
        return $user->createToken('auth_token', ['*'], $this->getTokenExpiryTime())->plainTextToken;
    }

    public function getTokenExpiryTime(): Carbon
    {
        return now()->addMonth();
    }

    public function getTokenType(): string
    {
        return 'Bearer';
    }
}

