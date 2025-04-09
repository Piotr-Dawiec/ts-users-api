<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_data_and_emails_can_be_updated()
    {
        $user = User::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'phone' => '123456789',
        ]);

        $user->emails()->createMany([
            ['email' => 'old@example.com'],
            ['email' => 'keep@example.com'],
        ]);

        $payload = [
            'first_name' => 'Adam',
            'last_name' => 'Nowak',
            'phone' => '999888777',
            'emails' => [
                'keep@example.com',
                'new@example.com',
            ],
        ];

        $response = $this->putJson("/api/users/{$user->id}", $payload);

        $response->assertStatus(200);
        $response->assertJsonFragment(['first_name' => 'Adam']);
        $response->assertJsonFragment(['email' => 'keep@example.com']);
        $response->assertJsonFragment(['email' => 'new@example.com']);
        $response->assertJsonMissing(['email' => 'old@example.com']);

        $this->assertDatabaseHas('emails', ['email' => 'keep@example.com']);
        $this->assertDatabaseHas('emails', ['email' => 'new@example.com']);
        $this->assertDatabaseMissing('emails', ['email' => 'old@example.com']);
    }

    public function test_email_cannot_be_reused_if_it_already_exists_in_database()
    {
        $user1 = User::factory()->create();
        $user1->emails()->create(['email' => 'duplikat@example.com']);

        $user2 = User::factory()->create();

        $payload = [
            'first_name' => $user2->first_name,
            'last_name' => $user2->last_name,
            'phone' => $user2->phone,
            'emails' => ['duplikat@example.com'],
        ];

        $response = $this->putJson("/api/users/{$user2->id}", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['emails.0']);
    }

    public function test_updating_nonexistent_user_returns_404()
    {
        $payload = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '000111222',
            'emails' => ['ghost@example.com'],
        ];

        $response = $this->putJson('/api/users/9999', $payload);

        $response->assertStatus(404);
    }


}

