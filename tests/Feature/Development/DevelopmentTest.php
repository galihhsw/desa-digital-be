<?php

namespace Tests\Feature\Development;

use App\Interfaces\DevelopmentRepositoryInterface;
use App\Models\Development;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Development $development;

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

        $this->development = Development::create([
            'thumbnail' => 'assets/developments/road.jpg',
            'name' => 'Pengaspalan Jalan Dusun',
            'description' => 'Perbaikan jalan penghubung dusun',
            'person_in_charge' => 'Ir. Bambang',
            'start_date' => '2026-06-01',
            'end_date' => '2026-08-31',
            'amount' => 150000000,
            'status' => 'ongoing',
        ]);
    }

    public function test_development_repository_update_persists_person_in_charge(): void
    {
        $repo = app(DevelopmentRepositoryInterface::class);

        $updated = $repo->update($this->development->id, [
            'name' => 'Pengaspalan Jalan Dusun Tahap 2',
            'description' => 'Perbaikan jalan penghubung dusun tahap 2',
            'person_in_charge' => 'Drs. Supardi M.T.',
            'amount' => 175000000,
            'start_date' => '2026-06-01',
            'end_date' => '2026-09-30',
            'status' => 'ongoing',
        ]);

        $this->assertEquals('Drs. Supardi M.T.', $updated->person_in_charge);
        $this->assertDatabaseHas('developments', [
            'id' => $this->development->id,
            'person_in_charge' => 'Drs. Supardi M.T.',
            'name' => 'Pengaspalan Jalan Dusun Tahap 2',
        ]);
    }
}
