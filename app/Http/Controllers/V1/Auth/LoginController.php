<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\V1\TokenService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(private readonly TokenService $tokenService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->successResponse('Login successful', [
            'user' =>  new UserResource($request->user()),
            'token' => $this->tokenService->generateSanctumToken($request->user()),
            'token_type' => $this->tokenService->getTokenType(),
            'token_expiry_tine' => $this->tokenService->getTokenExpiryTime(),
        ]);
    }
}
