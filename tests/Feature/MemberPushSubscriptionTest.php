<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberPushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_store_push_subscription_without_duplicate_endpoint(): void
    {
        $member = Member::create(['full_name' => 'Member Push', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);

        $payload = [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/example-endpoint',
            'keys' => [
                'p256dh' => str_repeat('a', 90),
                'auth' => str_repeat('b', 24),
            ],
            'content_encoding' => 'aes128gcm',
            'device_name' => 'Chrome Android',
        ];

        $this->actingAs($user)
            ->postJson(route('member.push-subscriptions.store'), $payload)
            ->assertOk()
            ->assertJsonPath('message', 'Notifikasi aktif di perangkat ini.');

        $this->actingAs($user)
            ->postJson(route('member.push-subscriptions.store'), $payload)
            ->assertOk();

        $this->assertSame(1, PushSubscription::count());
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'member_id' => $member->id,
            'endpoint' => $payload['endpoint'],
            'content_encoding' => 'aes128gcm',
            'revoked_at' => null,
        ]);
    }

    public function test_member_can_only_revoke_own_push_subscription(): void
    {
        $member = Member::create(['full_name' => 'Pemilik Push', 'member_status' => 'active']);
        $otherMember = Member::create(['full_name' => 'Member Lain Push', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);
        $otherUser = User::factory()->create(['member_id' => $otherMember->id, 'role' => 'member']);

        $ownSubscription = PushSubscription::create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/own-endpoint',
            'public_key' => 'public',
            'auth_token' => 'auth',
            'content_encoding' => 'aes128gcm',
        ]);
        $otherSubscription = PushSubscription::create([
            'user_id' => $otherUser->id,
            'member_id' => $otherMember->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/other-endpoint',
            'public_key' => 'public',
            'auth_token' => 'auth',
            'content_encoding' => 'aes128gcm',
        ]);

        $this->actingAs($user)
            ->deleteJson(route('member.push-subscriptions.destroy'), [
                'endpoint' => $otherSubscription->endpoint,
            ])
            ->assertOk();

        $this->assertNull($otherSubscription->fresh()->revoked_at);

        $this->actingAs($user)
            ->deleteJson(route('member.push-subscriptions.destroy'), [
                'endpoint' => $ownSubscription->endpoint,
            ])
            ->assertOk();

        $this->assertNotNull($ownSubscription->fresh()->revoked_at);
    }

    public function test_push_test_requires_vapid_configuration(): void
    {
        config([
            'services.webpush.vapid.public_key' => null,
            'services.webpush.vapid.private_key' => null,
            'services.webpush.vapid.subject' => null,
        ]);

        $member = Member::create(['full_name' => 'Member Tes Push', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);

        $this->actingAs($user)
            ->postJson(route('member.push-notifications.test'))
            ->assertStatus(422)
            ->assertJsonPath('message', 'VAPID key belum dikonfigurasi di server.');
    }
}
