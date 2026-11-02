<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_push_subscription(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('push.store'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-token-123',
            'keys' => [
                'auth' => 'test_auth_secret_key',
                'p256dh' => 'test_p256dh_public_key',
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertTrue($user->pushSubscriptions()->where('endpoint', 'https://fcm.googleapis.com/fcm/send/test-token-123')->exists());
    }
}
