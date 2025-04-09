<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_list_is_returned_with_emails()
    {
        $user1 = User::factory()->create(['first_name' => 'Anna']);
        $user2 = User::factory()->create(['first_name' => 'Tomasz']);

        $user1->emails()->create(['email' => 'anna@example.com']);
        $user2->emails()->create(['email' => 'tomek@example.com']);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonFragment(['first_name' => 'Anna']);
        $response->assertJsonFragment(['first_name' => 'Tomasz']);
        $response->assertJsonFragment(['email' => 'anna@example.com']);
        $response->assertJsonFragment(['email' => 'tomek@example.com']);
    }
}
