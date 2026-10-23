<?php

namespace Tests\Feature\FamilyMember;

use App\Interfaces\FamilyMemberRepositoryInterface;
use App\Models\FamilyMember;
use App\Models\HeadOfFamily;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class FamilyMemberTest extends TestCase
{
    use RefreshDatabase;

    protected User $citizenUser;
    protected HeadOfFamily $headOfFamily;
    protected User $memberUser;
    protected FamilyMember $familyMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

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

        $this->memberUser = User::create([
            'name' => 'Dewi Santoso',
            'email' => 'dewi@test.id',
            'password' => 'dewi12345',
        ]);

        $this->familyMember = FamilyMember::create([
            'user_id' => $this->memberUser->id,
            'head_of_family_id' => $this->headOfFamily->id,
            'profile_picture' => 'assets/family-members/dewi.jpg',
            'identity_number' => '3201234567890002',
            'gender' => 'female',
            'date_of_birth' => '1988-08-20',
            'phone_number' => '081234567899',
            'occupation' => 'Ibu Rumah Tangga',
            'marital_status' => 'married',
            'relation' => 'wife',
        ]);
    }

    public function test_family_member_update_preserves_password_when_omitted(): void
    {
        $originalHash = $this->memberUser->password;
        $repo = app(FamilyMemberRepositoryInterface::class);

        $repo->update($this->familyMember->id, [
            'name' => 'Dewi Santoso S.Pd',
            'email' => 'dewi@test.id',
            'identity_number' => '3201234567890002',
            'gender' => 'female',
            'date_of_birth' => '1988-08-20',
            'phone_number' => '089999999999',
            'occupation' => 'Guru',
            'marital_status' => 'married',
            'relation' => 'wife',
        ]);

        $this->memberUser->refresh();

        // Password hash unchanged
        $this->assertEquals($originalHash, $this->memberUser->password);

        // Member can still login with original password
        $canLogin = Auth::guard('web')->attempt([
            'email' => 'dewi@test.id',
            'password' => 'dewi12345',
        ]);
        $this->assertTrue($canLogin);
    }

    public function test_family_members_list_is_scoped_to_logged_in_head_of_family(): void
    {
        // Another household
        $otherUser = User::create([
            'name' => 'Agus',
            'email' => 'agus@test.id',
            'password' => 'agus123',
        ]);
        $otherUser->assignRole('head-of-family');

        $otherHof = HeadOfFamily::create([
            'user_id' => $otherUser->id,
            'profile_picture' => 'assets/dummy.jpg',
            'identity_number' => '3201234567891111',
            'gender' => 'male',
            'date_of_birth' => '1980-01-01',
            'phone_number' => '081111111111',
            'occupation' => 'PNS',
            'marital_status' => 'married',
        ]);

        $otherMemberUser = User::create([
            'name' => 'Anak Agus',
            'email' => 'anakagus@test.id',
            'password' => 'anak123',
        ]);

        FamilyMember::create([
            'user_id' => $otherMemberUser->id,
            'head_of_family_id' => $otherHof->id,
            'profile_picture' => 'assets/dummy.jpg',
            'identity_number' => '3201234567892222',
            'gender' => 'male',
            'date_of_birth' => '2010-01-01',
            'phone_number' => '082222222222',
            'occupation' => 'Pelajar',
            'marital_status' => 'single',
            'relation' => 'child',
        ]);

        // Login as first head of family
        $this->actingAs($this->citizenUser, 'sanctum');

        $response = $this->getJson('/api/family-member');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->familyMember->id, $data[0]['id']);
    }

    public function test_family_member_update_persists_relation_attribute(): void
    {
        $repo = app(FamilyMemberRepositoryInterface::class);

        $updated = $repo->update($this->familyMember->id, [
            'name' => 'Dewi Santoso',
            'email' => 'dewi@test.id',
            'identity_number' => '3201234567890002',
            'gender' => 'female',
            'date_of_birth' => '1988-08-20',
            'phone_number' => '081234567899',
            'occupation' => 'Ibu Rumah Tangga',
            'marital_status' => 'married',
            'relation' => 'child', // Changed relation
        ]);

        $this->assertEquals('child', $updated->relation);
        $this->assertDatabaseHas('family_members', [
            'id' => $this->familyMember->id,
            'relation' => 'child',
        ]);
    }
}
