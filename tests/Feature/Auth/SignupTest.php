<?php

use App\Models\User;

test('signup with valid data', function () {
    $response = $this->postJson('/v1/signup', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Signup successful',
            'data' => [
                'email' => 'john@example.com',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);
});

test('signup with duplicate email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/v1/signup', [
        'name' => 'John Doe',
        'email' => 'existing@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('signup with missing name', function () {
    $response = $this->postJson('/v1/signup', [
        'email' => 'john@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('signup with missing email', function () {
    $response = $this->postJson('/v1/signup', [
        'name' => 'John Doe',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('signup with missing password', function () {
    $response = $this->postJson('/v1/signup', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('signup with weak password', function () {
    $response = $this->postJson('/v1/signup', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

