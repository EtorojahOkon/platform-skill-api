<?php

namespace App\Services\V1;

use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotice;
use App\Services\V1\OtpService;

class SignupService
{
    public function __construct(private readonly OtpService $otpService) {}

    public function registerUser(array $data): User
    {
        $user = User::create($data);

        if (!$user) {
            throw new \Exception('Failed to create user');
        }

        $otp = $this->otpService->generateOtp();
        $expiry = $this->otpService->getExpiryTime();

        $user->notify(new VerifyEmailNotice($otp, $expiry));

        return $user;
    }

    public function verifyUser(User $user): bool
    {
        if ($user->email_verified_at) {
            return false;
        }

        return $user->forceFill(['email_verified_at' => now()])->save();
    }
}
