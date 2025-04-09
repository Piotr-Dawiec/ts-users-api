<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_soft_deleted_and_emails_remain()
    {
        $user = User::factory()->create();
        $user->emails()->create(['email' => 'delete@example.com']);

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertDatabaseHas('emails', ['email' => 'delete@example.com']);
    }

    public function test_deleting_nonexistent_user_returns_404()
    {
        $response = $this->deleteJson('/api/users/9999');

        $response->assertStatus(404);
    }

}
