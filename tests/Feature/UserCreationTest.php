<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_emails()
    {
        $payload = [
            'first_name' => 'Ania',
            'last_name' => 'Nowak',
            'phone' => '123456789',
            'emails' => [
                'ania@example.com',
                'nowak@example.com'
            ]
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'first_name' => 'Ania',
            'last_name' => 'Nowak',
            'phone' => '123456789'
        ]);

        $this->assertDatabaseHas('emails', ['email' => 'ania@example.com']);
        $this->assertDatabaseHas('emails', ['email' => 'nowak@example.com']);
    }

    public function test_user_creation_fails_with_empty_first_name()
    {
        $payload = [
            'first_name' => '',
            'last_name' => 'Nowak',
            'phone' => '123123123',
            'emails' => [
                'ania@example.com',
                'nowak@example.com'
            ]
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name']);
    }

    public function test_user_creation_fails_with_empty_last_name()
    {
        $payload = [
            'first_name' => 'Ania',
            'last_name' => '',
            'phone' => '123123123',
            'emails' => [
                'ania@example.com',
                'nowak@example.com'
            ]
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['last_name']);
    }
    
    public function test_user_creation_fails_with_empty_phone()
    {
        $payload = [
            'first_name' => 'Ania',
            'last_name' => 'Nowak',
            'phone' => '',
            'emails' => [
                'ania@example.com',
                'nowak@example.com'
            ]
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }
    
    public function test_user_creation_fails_with_long_phone()
    {
        $payload = [
            'first_name' => 'Ania',
            'last_name' => 'Nowak',
            'phone' => '88888888888888888888888888888',
            'emails' => [
                'ania@example.com',
                'nowak@example.com'
            ]
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    public function test_user_creation_fails_without_emails()
    {
        $payload = [
            'first_name' => 'Basia',
            'last_name' => 'Nowak',
            'phone' => '123123123'
            // no 'emails'
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['emails']);
    }

    public function test_user_creation_fails_with_empty_emails()
    {
        $payload = [
            'first_name' => 'Basia',
            'last_name' => 'Nowak',
            'phone' => '123123123',
            'emails' => [],
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['emails']);
    }

}
