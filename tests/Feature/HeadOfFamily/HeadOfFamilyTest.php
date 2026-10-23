<?php

namespace Tests\Feature\HeadOfFamily;

use App\Interfaces\HeadOfFamilyRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\HeadOfFamily;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HeadOfFamilyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $citizenUser;
    protected HeadOfFamily $headOfFamily;

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
    }

    public function test_head_of_family_update_preserves_password_when_password_field_is_empty(): void
    {
        $originalHash = $this->citizenUser->password;
        $repo = app(HeadOfFamilyRepositoryInterface::class);

        $repo->update($this->headOfFamily->id, [
            'name' => 'Budi Santoso S.Kom',
            'email' => 'budi@test.id',
            'identity_number' => '3201234567890001',
            'gender' => 'male',
            'date_of_birth' => '1985-05-12',
            'phone_number' => '089999999999',
            'occupation' => 'Wiraswasta',
            'marital_status' => 'married',
        ]);

        $this->citizenUser->refresh();

        // Name updated, password untouched
        $this->assertEquals('Budi Santoso S.Kom', $this->citizenUser->name);
        $this->assertEquals($originalHash, $this->citizenUser->password);

        // Can still login with existing password
        $canLogin = Auth::guard('web')->attempt([
            'email' => 'budi@test.id',
            'password' => 'warga12345',
        ]);
        $this->assertTrue($canLogin);
    }

    public function test_user_repository_update_persists_email_and_name(): void
    {
        $userRepo = app(UserRepositoryInterface::class);
        $userRepo->update($this->citizenUser->id, [
            'name' => 'Budi Santoso Baru',
            'email' => 'budi_baru@test.id',
        ]);

        $this->citizenUser->refresh();
        $this->assertEquals('Budi Santoso Baru', $this->citizenUser->name);
        $this->assertEquals('budi_baru@test.id', $this->citizenUser->email);
    }

    public function test_user_password_can_be_changed_and_verified(): void
    {
        $userRepo = app(UserRepositoryInterface::class);
        $userRepo->update($this->citizenUser->id, [
            'name' => 'Budi Santoso',
            'password' => 'newpassword789',
        ]);

        $this->citizenUser->refresh();
        $this->assertTrue(Hash::check('newpassword789', $this->citizenUser->password));

        $canLogin = Auth::guard('web')->attempt([
            'email' => 'budi@test.id',
            'password' => 'newpassword789',
        ]);
        $this->assertTrue($canLogin);
    }

    public function test_admin_can_retrieve_head_of_family_list(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $response = $this->getJson('/api/head-of-family');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'user', 'identity_number', 'phone_number'],
            ],
        ]);
    }
}
