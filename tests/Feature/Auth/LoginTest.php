<?php

use App\Models\User;

test('login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/v1/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
                'token_expiry_tine',
            ],
        ])
        ->assertJson([
            'message' => 'Login successful',
        ]);
});

test('login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/v1/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login with missing email', function () {
    $response = $this->postJson('/v1/login', [
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login with missing password', function () {
    $response = $this->postJson('/v1/login', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('login with invalid email format', function () {
    $response = $this->postJson('/v1/login', [
        'email' => 'invalid-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

