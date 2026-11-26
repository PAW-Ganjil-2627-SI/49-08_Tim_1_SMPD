<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\ReadingArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityAndAreaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_reading_area_and_facility_pages(): void
    {
        $area = ReadingArea::factory()->create();
        $facility = Facility::factory()->create();

        // Reading Area routes
        $this->get(route('reading-areas.index'))->assertRedirect(route('login'));
        $this->get(route('reading-areas.create'))->assertRedirect(route('login'));
        $this->post(route('reading-areas.store'), [])->assertRedirect(route('login'));
        $this->get(route('reading-areas.show', $area))->assertRedirect(route('login'));
        $this->get(route('reading-areas.edit', $area))->assertRedirect(route('login'));
        $this->put(route('reading-areas.update', $area), [])->assertRedirect(route('login'));
        $this->delete(route('reading-areas.destroy', $area))->assertRedirect(route('login'));

        // Facility routes
        $this->get(route('facilities.index'))->assertRedirect(route('login'));
        $this->get(route('facilities.create'))->assertRedirect(route('login'));
        $this->post(route('facilities.store'), [])->assertRedirect(route('login'));
        $this->get(route('facilities.show', $facility))->assertRedirect(route('login'));
        $this->get(route('facilities.edit', $facility))->assertRedirect(route('login'));
        $this->put(route('facilities.update', $facility), [])->assertRedirect(route('login'));
        $this->delete(route('facilities.destroy', $facility))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_reading_areas_index(): void
    {
        $user = User::factory()->create();
        $facility = Facility::create([
            'name' => 'Wi-Fi 6 Dedicated',
            'type' => 'jaringan',
            'status' => 'available',
        ]);

        $area = ReadingArea::create([
            'name' => 'Ruang Riset Senyap',
            'floor_number' => 2,
            'capacity' => 15,
            'status' => 'available',
            'description' => 'Khusus membaca tanpa suara.',
        ]);

        $area->facilities()->attach($facility->id);

        $response = $this->actingAs($user)->get(route('reading-areas.index'));

        $response->assertOk();
        $response->assertViewIs('reading_areas.index');
        $response->assertSee('Ruang Riset Senyap');
        $response->assertSee('Lantai 2');
        $response->assertSee('Wi-Fi 6 Dedicated');
        $response->assertSee(route('reading-areas.create'));
    }

    public function test_authenticated_user_can_create_reading_area_with_facilities(): void
    {
        $user = User::factory()->create();
        $facility1 = Facility::create(['name' => 'AC Sentral', 'type' => 'kenyamanan', 'status' => 'available']);
        $facility2 = Facility::create(['name' => 'Stopkontak Meja', 'type' => 'kelistrikan', 'status' => 'available']);

        $response = $this->actingAs($user)->post(route('reading-areas.store'), [
            'name' => 'Bilik Diskusi C-01',
            'floor_number' => 3,
            'capacity' => 6,
            'description' => 'Ruang diskusi kecil dilengkapi layar presentasi.',
            'status' => 'available',
            'facilities' => [$facility1->id, $facility2->id],
        ]);

        $response->assertRedirect(route('reading-areas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reading_areas', [
            'name' => 'Bilik Diskusi C-01',
            'floor_number' => 3,
            'capacity' => 6,
            'status' => 'available',
        ]);

        $area = ReadingArea::where('name', 'Bilik Diskusi C-01')->firstOrFail();
        $this->assertCount(2, $area->facilities);
        $this->assertTrue($area->facilities->contains($facility1));
        $this->assertTrue($area->facilities->contains($facility2));
    }

    public function test_authenticated_user_can_view_reading_area_detail(): void
    {
        $user = User::factory()->create();
        $facility = Facility::create(['name' => 'Lampu Meja LED', 'type' => 'kelistrikan', 'status' => 'available']);
        $area = ReadingArea::create([
            'name' => 'Bilik Mandiri Eksklusif',
            'floor_number' => 1,
            'capacity' => 1,
            'status' => 'available',
            'description' => 'Area belajar perseorangan.',
        ]);
        $area->facilities()->attach($facility->id);

        $response = $this->actingAs($user)->get(route('reading-areas.show', $area));

        $response->assertOk();
        $response->assertViewIs('reading_areas.show');
        $response->assertSee('Bilik Mandiri Eksklusif');
        $response->assertSee('Lampu Meja LED');
        $response->assertSee('Lantai 1');
    }

    public function test_authenticated_user_can_update_reading_area_and_sync_facilities(): void
    {
        $user = User::factory()->create();
        $facA = Facility::create(['name' => 'Fasilitas A', 'type' => 'elektronik', 'status' => 'available']);
        $facB = Facility::create(['name' => 'Fasilitas B', 'type' => 'jaringan', 'status' => 'available']);

        $area = ReadingArea::create([
            'name' => 'Nama Lama',
            'floor_number' => 1,
            'capacity' => 5,
            'status' => 'available',
        ]);
        $area->facilities()->attach($facA->id);

        $response = $this->actingAs($user)->put(route('reading-areas.update', $area), [
            'name' => 'Nama Baru Diperbarui',
            'floor_number' => 2,
            'capacity' => 10,
            'description' => 'Deskripsi baru.',
            'status' => 'unavailable',
            'facilities' => [$facB->id], // ganti facA menjadi facB
        ]);

        $response->assertRedirect(route('reading-areas.index'));
        $this->assertDatabaseHas('reading_areas', [
            'id' => $area->id,
            'name' => 'Nama Baru Diperbarui',
            'floor_number' => 2,
            'capacity' => 10,
            'status' => 'unavailable',
        ]);

        $area->refresh();
        $this->assertCount(1, $area->facilities);
        $this->assertTrue($area->facilities->contains($facB));
        $this->assertFalse($area->facilities->contains($facA));
    }

    public function test_authenticated_user_can_delete_reading_area(): void
    {
        $user = User::factory()->create();
        $facility = Facility::create(['name' => 'Kursi Ergonomis', 'type' => 'furnitur', 'status' => 'available']);
        $area = ReadingArea::create([
            'name' => 'Area Yang Akan Dihapus',
            'floor_number' => 1,
            'capacity' => 4,
            'status' => 'available',
        ]);
        $area->facilities()->attach($facility->id);

        $response = $this->actingAs($user)->delete(route('reading-areas.destroy', $area));

        $response->assertRedirect(route('reading-areas.index'));
        $this->assertDatabaseMissing('reading_areas', ['id' => $area->id]);

        // Verifikasi tabel pivot terhapus (cascade) namun fasilitas asli tetap ada
        $this->assertDatabaseMissing('area_facilities', ['area_id' => $area->id]);
        $this->assertDatabaseHas('facilities', ['id' => $facility->id]);
    }

    public function test_authenticated_user_can_view_facilities_index(): void
    {
        $user = User::factory()->create();
        $area = ReadingArea::create([
            'name' => 'Ruang Baca Utama',
            'floor_number' => 1,
            'capacity' => 50,
            'status' => 'available',
        ]);
        $facility = Facility::create([
            'name' => 'Proyektor HD',
            'type' => 'elektronik',
            'status' => 'available',
        ]);
        $facility->readingAreas()->attach($area->id);

        $response = $this->actingAs($user)->get(route('facilities.index'));

        $response->assertOk();
        $response->assertViewIs('facilities.index');
        $response->assertSee('Proyektor HD');
        $response->assertSee('elektronik');
        $response->assertSee('Ruang Baca Utama');
    }

    public function test_authenticated_user_can_create_facility_and_map_to_reading_areas(): void
    {
        $user = User::factory()->create();
        $area1 = ReadingArea::create(['name' => 'Area 1', 'floor_number' => 1, 'capacity' => 10, 'status' => 'available']);
        $area2 = ReadingArea::create(['name' => 'Area 2', 'floor_number' => 2, 'capacity' => 20, 'status' => 'available']);

        $response = $this->actingAs($user)->post(route('facilities.store'), [
            'name' => 'Papan Tulis Kaca',
            'type' => 'furnitur',
            'description' => 'Papan tulis kaca untuk brainstorming kelompok.',
            'status' => 'available',
            'reading_areas' => [$area1->id, $area2->id],
        ]);

        $response->assertRedirect(route('facilities.index'));
        $this->assertDatabaseHas('facilities', [
            'name' => 'Papan Tulis Kaca',
            'type' => 'furnitur',
        ]);

        $facility = Facility::where('name', 'Papan Tulis Kaca')->firstOrFail();
        $this->assertCount(2, $facility->readingAreas);
        $this->assertTrue($facility->readingAreas->contains($area1));
        $this->assertTrue($facility->readingAreas->contains($area2));
    }

    public function test_authenticated_user_can_view_facility_detail(): void
    {
        $user = User::factory()->create();
        $area = ReadingArea::create(['name' => 'Zona Santai', 'floor_number' => 1, 'capacity' => 12, 'status' => 'available']);
        $facility = Facility::create([
            'name' => 'Sofa Beanbag',
            'type' => 'kenyamanan',
            'description' => 'Sofa santai busa empuk.',
            'status' => 'available',
        ]);
        $facility->readingAreas()->attach($area->id);

        $response = $this->actingAs($user)->get(route('facilities.show', $facility));

        $response->assertOk();
        $response->assertViewIs('facilities.show');
        $response->assertSee('Sofa Beanbag');
        $response->assertSee('Zona Santai');
        $response->assertSee('kenyamanan');
    }

    public function test_authenticated_user_can_update_facility_and_sync_reading_areas(): void
    {
        $user = User::factory()->create();
        $areaA = ReadingArea::create(['name' => 'Area A', 'floor_number' => 1, 'capacity' => 5, 'status' => 'available']);
        $areaB = ReadingArea::create(['name' => 'Area B', 'floor_number' => 2, 'capacity' => 5, 'status' => 'available']);

        $facility = Facility::create([
            'name' => 'Scanner Dokumen',
            'type' => 'elektronik',
            'status' => 'available',
        ]);
        $facility->readingAreas()->attach($areaA->id);

        $response = $this->actingAs($user)->put(route('facilities.update', $facility), [
            'name' => 'Scanner Dokumen Flatbed',
            'type' => 'elektronik',
            'description' => 'Resolusi tinggi 1200 dpi.',
            'status' => 'unavailable',
            'reading_areas' => [$areaB->id],
        ]);

        $response->assertRedirect(route('facilities.index'));
        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'name' => 'Scanner Dokumen Flatbed',
            'status' => 'unavailable',
        ]);

        $facility->refresh();
        $this->assertCount(1, $facility->readingAreas);
        $this->assertTrue($facility->readingAreas->contains($areaB));
        $this->assertFalse($facility->readingAreas->contains($areaA));
    }

    public function test_authenticated_user_can_delete_facility(): void
    {
        $user = User::factory()->create();
        $area = ReadingArea::create(['name' => 'Area Tetap Ada', 'floor_number' => 1, 'capacity' => 5, 'status' => 'available']);
        $facility = Facility::create([
            'name' => 'Fasilitas Dihapus',
            'type' => 'jaringan',
            'status' => 'available',
        ]);
        $facility->readingAreas()->attach($area->id);

        $response = $this->actingAs($user)->delete(route('facilities.destroy', $facility));

        $response->assertRedirect(route('facilities.index'));
        $this->assertDatabaseMissing('facilities', ['id' => $facility->id]);

        // Pivot terhapus, area baca tetap utuh
        $this->assertDatabaseMissing('area_facilities', ['facility_id' => $facility->id]);
        $this->assertDatabaseHas('reading_areas', ['id' => $area->id]);
    }
}

