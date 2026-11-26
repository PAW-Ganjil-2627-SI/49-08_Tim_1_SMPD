<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\User;
use Database\Seeders\PublisherSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublisherTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $publisher = Publisher::factory()->create();

        $this->get(route('publishers.index'))->assertRedirect(route('login'));
        $this->get(route('publishers.create'))->assertRedirect(route('login'));
        $this->post(route('publishers.store'), [])->assertRedirect(route('login'));
        $this->get(route('publishers.show', $publisher))->assertRedirect(route('login'));
        $this->get(route('publishers.edit', $publisher))->assertRedirect(route('login'));
        $this->put(route('publishers.update', $publisher), [])->assertRedirect(route('login'));
        $this->delete(route('publishers.destroy', $publisher))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_publishers_index(): void
    {
        $publisher = Publisher::factory()->publisher()->create([
            'name' => 'PT Penerbit Nusantara',
            'region' => 'Bandung',
            'contact_person' => 'Ahmad Sales',
        ]);
        $distributor = Publisher::factory()->distributor()->create([
            'name' => 'CV Distribusi Utama',
            'region' => 'Jakarta',
            'contact_person' => 'Bambang Distributor',
        ]);

        $response = $this->actingAs($this->user)->get(route('publishers.index'));

        $response->assertOk();
        $response->assertViewIs('publishers.index');
        $response->assertSee('PT Penerbit Nusantara');
        $response->assertSee('CV Distribusi Utama');
        $response->assertSee('Bandung');
        $response->assertSee('Jakarta');
        $response->assertSee('Ahmad Sales');
        $response->assertSee('Bambang Distributor');
        $response->assertSee('Penerbit');
        $response->assertSee('Distributor');
    }

    public function test_user_can_filter_and_search_publishers(): void
    {
        Publisher::factory()->publisher()->create([
            'name' => 'Penerbit Mizan',
            'region' => 'Bandung',
        ]);
        Publisher::factory()->distributor()->create([
            'name' => 'Distributor Erlangga Niaga',
            'region' => 'Jakarta',
        ]);

        // Search by keyword
        $responseSearch = $this->actingAs($this->user)
            ->get(route('publishers.index', ['search' => 'Mizan']));

        $responseSearch->assertOk();
        $responseSearch->assertSee('Penerbit Mizan');
        $responseSearch->assertDontSee('Distributor Erlangga Niaga');

        // Filter by type
        $responseType = $this->actingAs($this->user)
            ->get(route('publishers.index', ['type' => 'distributor']));

        $responseType->assertOk();
        $responseType->assertSee('Distributor Erlangga Niaga');
        $responseType->assertDontSee('Penerbit Mizan');
    }

    public function test_authenticated_user_can_view_create_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('publishers.create'));

        $response->assertOk();
        $response->assertViewIs('publishers.create');
        $response->assertSee('Tambah Penerbit / Supplier');
        $response->assertSee('action="'.route('publishers.store').'"', false);
    }

    public function test_authenticated_user_can_store_new_publisher(): void
    {
        $payload = [
            'name' => 'PT Pustaka Abadi Jaya',
            'type' => 'publisher',
            'contact_person' => 'Dewi Anggraeni',
            'phone' => '081298765432',
            'email' => 'dewi@pustakaabadi.id',
            'region' => 'Yogyakarta',
            'address' => 'Jl. Malioboro No. 12, Yogyakarta',
            'notes' => 'Diskon reguler 25%, tempo 30 hari',
        ];

        $response = $this->actingAs($this->user)->post(route('publishers.store'), $payload);

        $response->assertRedirect(route('publishers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('publishers', [
            'name' => 'PT Pustaka Abadi Jaya',
            'type' => 'publisher',
            'contact_person' => 'Dewi Anggraeni',
            'email' => 'dewi@pustakaabadi.id',
            'region' => 'Yogyakarta',
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->actingAs($this->user)->post(route('publishers.store'), [
            'name' => '',
            'type' => 'invalid_type',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'type', 'email']);
    }

    public function test_authenticated_user_can_view_publisher_detail(): void
    {
        $publisher = Publisher::factory()->create([
            'name' => 'Gramedia Pustaka',
            'contact_person' => 'Rina Sales',
            'phone' => '081122334455',
            'region' => 'Jakarta Barat',
            'address' => 'Jl. Palmerah Barat No. 29',
            'notes' => 'Mitra pengadaan buku utama',
        ]);

        $response = $this->actingAs($this->user)->get(route('publishers.show', $publisher));

        $response->assertOk();
        $response->assertViewIs('publishers.show');
        $response->assertSee('Gramedia Pustaka');
        $response->assertSee('Rina Sales');
        $response->assertSee('081122334455');
        $response->assertSee('Jakarta Barat');
        $response->assertSee('Jl. Palmerah Barat No. 29');
        $response->assertSee('Mitra pengadaan buku utama');
    }

    public function test_authenticated_user_can_view_edit_page(): void
    {
        $publisher = Publisher::factory()->create(['name' => 'Elex Media']);

        $response = $this->actingAs($this->user)->get(route('publishers.edit', $publisher));

        $response->assertOk();
        $response->assertViewIs('publishers.edit');
        $response->assertSee('Elex Media');
        $response->assertSee(route('publishers.update', $publisher));
    }

    public function test_authenticated_user_can_update_publisher(): void
    {
        $publisher = Publisher::factory()->create([
            'name' => 'Nama Lama',
            'type' => 'publisher',
            'region' => 'Solo',
        ]);

        $payload = [
            'name' => 'Nama Baru Yang Diperbarui',
            'type' => 'distributor',
            'contact_person' => 'Hendra Subagyo',
            'phone' => '081234567890',
            'email' => 'hendra@namabaru.co.id',
            'region' => 'Surabaya',
            'address' => 'Jl. Pemuda No. 45, Surabaya',
            'notes' => 'Diubah menjadi distributor',
        ];

        $response = $this->actingAs($this->user)->put(route('publishers.update', $publisher), $payload);

        $response->assertRedirect(route('publishers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('publishers', [
            'id' => $publisher->id,
            'name' => 'Nama Baru Yang Diperbarui',
            'type' => 'distributor',
            'region' => 'Surabaya',
            'contact_person' => 'Hendra Subagyo',
        ]);
    }

    public function test_authenticated_user_can_delete_publisher_without_books(): void
    {
        $publisher = Publisher::factory()->create(['name' => 'Penerbit Dihapus']);

        $response = $this->actingAs($this->user)->delete(route('publishers.destroy', $publisher));

        $response->assertRedirect(route('publishers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('publishers', [
            'id' => $publisher->id,
        ]);
    }

    public function test_publisher_with_books_cannot_be_deleted(): void
    {
        $publisher = Publisher::factory()->create(['name' => 'Penerbit Berbuku']);
        $category = Category::factory()->create();

        Book::factory()->create([
            'publisher_id' => $publisher->id,
            'category_id' => $category->id,
            'title' => 'Buku Terkait Penerbit',
        ]);

        $response = $this->actingAs($this->user)->delete(route('publishers.destroy', $publisher));

        $response->assertRedirect(route('publishers.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('publishers', [
            'id' => $publisher->id,
            'name' => 'Penerbit Berbuku',
        ]);
    }

    public function test_publisher_seeder_populates_data(): void
    {
        $this->seed(PublisherSeeder::class);

        $this->assertDatabaseCount('publishers', 6);
        $this->assertDatabaseHas('publishers', [
            'name' => 'Gramedia Pustaka Utama',
            'type' => 'publisher',
        ]);
        $this->assertDatabaseHas('publishers', [
            'name' => 'CV Niaga Pustaka Mandiri',
            'type' => 'distributor',
        ]);
    }
}
