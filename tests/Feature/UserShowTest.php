<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_fetched_with_emails()
    {
        $user = User::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'phone' => '123456789',
        ]);

        $user->emails()->createMany([
            ['email' => 'jan@example.com'],
            ['email' => 'kowalski@example.com'],
        ]);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'first_name' => 'Jan',
                'last_name' => 'Kowalski',
                'phone' => '123456789',
                'emails' => [
                    ['email' => 'jan@example.com'],
                    ['email' => 'kowalski@example.com'],
                ]
            ]);
    }

    public function test_not_found_is_returned_when_user_does_not_exist()
    {
        $response = $this->getJson('/api/users/9999');

        $response->assertStatus(404);
    }

}
