<?php

namespace App\Services\V1;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class OtpService
{
    public static function generateOtp(int $length = 6): string
    {
        return str_pad(random_int(0, 999999), $length, '0', STR_PAD_LEFT);
    }

    public function getExpiryTime(): Carbon
    {
        return now()->addMinutes(15);
    }

    public function storeOtp(string $key, string $otp) : void
    {
        Cache::put($key, $otp, $this->getExpiryTime());
    }

    public function getCachedOtp(string $key): ?string
    {
        return Cache::get($key);
    }

    public function verifyOtp(string $key, string $otp): bool
    {
        return $this->getCachedOtp($key) === $otp;
    }

    public function clearOtp(string $key): void
    {
        Cache::forget($key);
    }
}
