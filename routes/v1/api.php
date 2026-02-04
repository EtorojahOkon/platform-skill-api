<?php

use App\Http\Controllers\V1\Auth\LoginController;
use App\Http\Controllers\V1\Auth\SignupController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);

Route::controller(SignupController::class)->group(function () {
    Route::post('signup', 'signup');
    Route::post('verify-email', 'verifyEmail');
});