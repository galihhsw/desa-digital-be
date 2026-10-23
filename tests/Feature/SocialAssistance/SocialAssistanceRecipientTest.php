<?php

namespace Tests\Feature\SocialAssistance;

use App\Interfaces\SocialAssistanceRecipientRepositoryInterface;
use App\Models\HeadOfFamily;
use App\Models\SocialAssistance;
use App\Models\SocialAssistanceRecipient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialAssistanceRecipientTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $citizenUser;
    protected HeadOfFamily $headOfFamily;
    protected SocialAssistance $assistance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->admin = User::create([
            'name' => 'Admin Desa',
            'email' => 'admin@test.id',
            'password' => 'password123',
        ]);
        $this->admin->assignRole('admin');

        $this->citizenUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@test.id',
            'password' => 'warga12345',
        ]);
        $this->citizenUser->assignRole('head-of-family');

        $this->headOfFamily = HeadOfFamily::create([
            'user_id' => $this->citizenUser->id,
            'profile_picture' => 'assets/head-of-families/dummy.jpg',
            'identity_number' => '3201234567890001',
            'gender' => 'male',
            'date_of_birth' => '1985-05-12',
            'phone_number' => '081234567890',
            'occupation' => 'Wiraswasta',
            'marital_status' => 'married',
        ]);

        $this->assistance = SocialAssistance::create([
            'thumbnail' => 'assets/social-assistance/bansos.jpg',
            'name' => 'BLT Dana Desa 2026',
            'category' => 'cash',
            'amount' => 600000,
            'provider' => 'Kemensos',
            'is_available' => true,
            'description' => 'Bantuan Langsung Tunai Desa',
        ]);
    }

    public function test_recipient_status_update_does_not_crash_and_saves_approved(): void
    {
        $recipient = SocialAssistanceRecipient::create([
            'social_assistance_id' => $this->assistance->id,
            'head_of_family_id' => $this->headOfFamily->id,
            'amount' => 600000,
            'reason' => 'Terdampak bencana',
            'bank' => 'bri',
            'account_number' => '1234567890',
            'proof' => 'assets/social-assistance-recipient/proof.jpg',
            'status' => 'pending',
        ]);

        $repo = app(SocialAssistanceRecipientRepositoryInterface::class);

        $updated = $repo->update($recipient->id, [
            'social_assistance_id' => $this->assistance->id,
            'head_of_family_id' => $this->headOfFamily->id,
            'amount' => 600000,
            'reason' => 'Terdampak bencana',
            'bank' => 'bri',
            'account_number' => '1234567890',
            'status' => 'approved',
        ]);

        $this->assertEquals('approved', $updated->status);
        $this->assertDatabaseHas('social_assistance_recipients', [
            'id' => $recipient->id,
            'status' => 'approved',
        ]);
    }

    public function test_recipient_status_can_be_rejected(): void
    {
        $recipient = SocialAssistanceRecipient::create([
            'social_assistance_id' => $this->assistance->id,
            'head_of_family_id' => $this->headOfFamily->id,
            'amount' => 600000,
            'reason' => 'Terdampak bencana',
            'bank' => 'bri',
            'account_number' => '1234567890',
            'proof' => 'assets/social-assistance-recipient/proof.jpg',
            'status' => 'pending',
        ]);

        $repo = app(SocialAssistanceRecipientRepositoryInterface::class);

        $updated = $repo->update($recipient->id, [
            'social_assistance_id' => $this->assistance->id,
            'head_of_family_id' => $this->headOfFamily->id,
            'amount' => 600000,
            'reason' => 'Terdampak bencana',
            'bank' => 'bri',
            'account_number' => '1234567890',
            'status' => 'rejected',
        ]);

        $this->assertEquals('rejected', $updated->status);
        $this->assertDatabaseHas('social_assistance_recipients', [
            'id' => $recipient->id,
            'status' => 'rejected',
        ]);
    }

    public function test_recipients_are_scoped_for_head_of_family(): void
    {
        // Recipient for current citizen
        SocialAssistanceRecipient::create([
            'social_assistance_id' => $this->assistance->id,
            'head_of_family_id' => $this->headOfFamily->id,
            'amount' => 600000,
            'reason' => 'Permohonan warga 1',
            'bank' => 'bri',
            'account_number' => '111111111',
            'status' => 'pending',
        ]);

        // Another citizen
        $otherUser = User::create([
            'name' => 'Warga Lain',
            'email' => 'other@test.id',
            'password' => 'secret123',
        ]);
        $otherUser->assignRole('head-of-family');

        $otherHof = HeadOfFamily::create([
            'user_id' => $otherUser->id,
            'profile_picture' => 'assets/dummy.jpg',
            'identity_number' => '3201234567899999',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone_number' => '081299999999',
            'occupation' => 'Buruh',
            'marital_status' => 'married',
        ]);

        SocialAssistanceRecipient::create([
            'social_assistance_id' => $this->assistance->id,
            'head_of_family_id' => $otherHof->id,
            'amount' => 600000,
            'reason' => 'Permohonan warga 2',
            'bank' => 'bni',
            'account_number' => '222222222',
            'status' => 'pending',
        ]);

        // Login as first citizen
        $this->actingAs($this->citizenUser, 'sanctum');

        $response = $this->getJson('/api/social-assistance-recipient');
        $response->assertStatus(200);

        // Citizen should only see their own application
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->headOfFamily->id, $data[0]['head_of_family']['id']);
    }
}
