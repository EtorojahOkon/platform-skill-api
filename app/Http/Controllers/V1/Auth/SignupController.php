<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use App\Notifications\Auth\WelcomeNotice;
use App\Services\V1\SignupService;
use App\Services\V1\TokenService;
use Illuminate\Http\JsonResponse;

class SignupController extends Controller
{
    public function __construct(
        private readonly SignupService $signupService,
        private readonly TokenService $tokenService
    ) {}

    public function signup(SignupRequest $request): JsonResponse
    {
        $user = $this->signupService->registerUser($request->validated());

        return $this->successResponse('Signup successful', [
            'email' => $user->email,
        ]);
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$this->signupService->verifyUser($user)) {
            return $this->validationErrorResponse('email', 'Email verification failed');
        }

        $user->notify(new WelcomeNotice);

        return $this->successResponse('Email verified successfully', [
            'token' => $this->tokenService->generateSanctumToken($user),
            'token_type' => $this->tokenService->getTokenType(),
            'token_expiry_time' => $this->tokenService->getTokenExpiryTime(),
        ]);
    }
}
