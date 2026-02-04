<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('verify email with valid code', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'email_verified_at' => null,
    ]);

    $code = '123456';
    Cache::put("EMAIL_VERIFICATION_{$user->email}", $code, now()->addMinutes(10));

    $response = $this->postJson('/v1/verify-email', [
        'email' => 'test@example.com',
        'code' => $code,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Email verified successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'token',
                'token_type',
                'token_expiry_tine',
            ],
        ]);

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();
});

test('verify email with invalid code', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'email_verified_at' => null,
    ]);

    Cache::put("EMAIL_VERIFICATION_{$user->email}", '123456', now()->addMinutes(10));

    $response = $this->postJson('/v1/verify-email', [
        'email' => 'test@example.com',
        'code' => '999999',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('verify email with non-existent email', function () {
    $response = $this->postJson('/v1/verify-email', [
        'email' => 'nonexistent@example.com',
        'code' => '123456',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('verify email with missing code', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/v1/verify-email', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('verify email with missing email', function () {
    $response = $this->postJson('/v1/verify-email', [
        'code' => '123456',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('verify already verified email', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'email_verified_at' => now(),
    ]);

    $code = '123456';
    Cache::put("EMAIL_VERIFICATION_{$user->email}", $code, now()->addMinutes(10));

    $response = $this->postJson('/v1/verify-email', [
        'email' => 'test@example.com',
        'code' => $code,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'Email verification failed',
        ]);
});

