<?php

namespace Tests\Feature\Event;

use App\Interfaces\EventParticipantRepositoryInterface;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\HeadOfFamily;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventParticipantTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $citizenUser;
    protected HeadOfFamily $headOfFamily;
    protected Event $event;

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

        $this->event = Event::create([
            'thumbnail' => 'assets/events/event.jpg',
            'name' => 'Pesta Rakyat Desa 2026',
            'price' => 25000,
            'description' => 'Festival tahunan desa',
            'date' => '2026-11-15',
            'time' => '08:00:00',
        ]);
    }

    public function test_event_participant_status_filtering_works_correctly(): void
    {
        $p1 = EventParticipant::create([
            'event_id' => $this->event->id,
            'head_of_family_id' => $this->headOfFamily->id,
            'quantity' => 2,
            'total_price' => 50000,
            'payment_status' => 'paid',
        ]);

        $p2 = EventParticipant::create([
            'event_id' => $this->event->id,
            'head_of_family_id' => $this->headOfFamily->id,
            'quantity' => 1,
            'total_price' => 25000,
            'payment_status' => 'pending',
        ]);

        $repo = app(EventParticipantRepositoryInterface::class);

        $this->actingAs($this->admin, 'sanctum');

        // Test filter 'paid'
        $paidResults = $repo->getAll(null, 'paid', null, true);
        $this->assertCount(1, $paidResults);
        $this->assertEquals('paid', $paidResults->first()->payment_status);
        $this->assertEquals($p1->id, $paidResults->first()->id);

        // Test filter 'pending'
        $pendingResults = $repo->getAll(null, 'pending', null, true);
        $this->assertCount(1, $pendingResults);
        $this->assertEquals('pending', $pendingResults->first()->payment_status);
        $this->assertEquals($p2->id, $pendingResults->first()->id);
    }

    public function test_paginated_event_participant_endpoint_filters_by_status(): void
    {
        EventParticipant::create([
            'event_id' => $this->event->id,
            'head_of_family_id' => $this->headOfFamily->id,
            'quantity' => 2,
            'total_price' => 50000,
            'payment_status' => 'paid',
        ]);

        EventParticipant::create([
            'event_id' => $this->event->id,
            'head_of_family_id' => $this->headOfFamily->id,
            'quantity' => 1,
            'total_price' => 25000,
            'payment_status' => 'pending',
        ]);

        $this->actingAs($this->admin, 'sanctum');

        $response = $this->getJson('/api/event-participant/all/paginated?status=paid&row_per_page=10');
        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('paid', $data[0]['payment_status']);
    }

    public function test_event_participant_resource_structure_has_scalar_ids_and_nested_resources(): void
    {
        $participant = EventParticipant::create([
            'event_id' => $this->event->id,
            'head_of_family_id' => $this->headOfFamily->id,
            'quantity' => 2,
            'total_price' => 50000,
            'payment_status' => 'paid',
        ]);

        $this->actingAs($this->admin, 'sanctum');

        $response = $this->getJson('/api/event-participant/' . $participant->id);
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($this->event->id, $data['event_id']);
        $this->assertEquals($this->headOfFamily->id, $data['head_of_family_id']);
        $this->assertIsArray($data['event']);
        $this->assertEquals($this->event->name, $data['event']['name']);
        $this->assertIsArray($data['head_of_family']);
    }
}
