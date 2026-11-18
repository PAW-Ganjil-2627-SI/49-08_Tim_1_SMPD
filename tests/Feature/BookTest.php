<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PDOException;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petugas = User::factory()->petugas()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Akses & otorisasi
    |--------------------------------------------------------------------------
    */

    public function test_guests_cannot_access_any_book_endpoint(): void
    {
        $book = $this->createBook();
        $login = route('login', absolute: false);

        foreach (['/books', '/books/create', "/books/{$book->id}", "/books/{$book->id}/edit"] as $uri) {
            $this->get($uri)->assertRedirect($login);
        }

        $this->post('/books', $this->validPayload())->assertRedirect($login);
        $this->put("/books/{$book->id}", $this->validPayload())->assertRedirect($login);
        $this->delete("/books/{$book->id}")->assertRedirect($login);

        $this->assertDatabaseCount('books', 1);
        $this->assertModelExists($book);
    }

    public function test_admin_and_petugas_can_open_every_book_page(): void
    {
        $book = $this->createBook();

        foreach ([User::factory()->admin()->create(), $this->petugas] as $user) {
            foreach (['/books', '/books/create', "/books/{$book->id}", "/books/{$book->id}/edit"] as $uri) {
                $this->actingAs($user)->get($uri)->assertOk();
            }
        }
    }

    public function test_users_with_other_roles_are_forbidden(): void
    {
        // Role di luar admin/petugas harus ditolak oleh server, bukan hanya disembunyikan di tampilan.
        $outsider = User::factory()->make(['id' => 999, 'role' => 'anggota']);
        $book = $this->createBook();

        foreach (['/books', '/books/create', "/books/{$book->id}", "/books/{$book->id}/edit"] as $uri) {
            $this->actingAs($outsider)->get($uri)->assertForbidden();
        }

        $this->actingAs($outsider)->post('/books', $this->validPayload())->assertForbidden();
        $this->actingAs($outsider)->put("/books/{$book->id}", $this->validPayload())->assertForbidden();
        $this->actingAs($outsider)->delete("/books/{$book->id}")->assertForbidden();

        $this->assertDatabaseCount('books', 1);
        $this->assertModelExists($book);
    }

    public function test_sidebar_marks_catalog_menu_as_active(): void
    {
        $this->actingAs($this->petugas)->get('/books')
            ->assertSee('href="'.route('books.index').'"', false)
            ->assertSee('aria-current="page"', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Daftar buku
    |--------------------------------------------------------------------------
    */

    public function test_index_shows_message_when_there_are_no_books(): void
    {
        $this->actingAs($this->petugas)->get('/books')
            ->assertOk()
            ->assertSee('Belum ada buku');
    }

    public function test_index_lists_books_with_relation_names(): void
    {
        $book = $this->createBook([
            'title' => 'Laskar Pelangi', 'isbn' => '9789793062792', 'publication_year' => 2005, 'stock' => 7, 'status' => 'unavailable',
        ], category: 'Fiksi', publisher: 'Bentang Pustaka', authors: ['Andrea Hirata', 'Penulis Pendamping']);

        $this->actingAs($this->petugas)->get('/books')
            ->assertOk()
            ->assertSee('Laskar Pelangi')
            ->assertSee('9789793062792')
            ->assertSee('Fiksi')
            ->assertSee('Bentang Pustaka')
            ->assertSee('Andrea Hirata, Penulis Pendamping')
            ->assertSee('2005')
            ->assertSee('Tidak tersedia')
            ->assertSee(route('books.show', $book))
            ->assertSee(route('books.edit', $book));
    }

    public function test_index_is_paginated_with_ten_books_per_page(): void
    {
        foreach (range(1, 12) as $number) {
            $this->createBook(['title' => 'Buku Nomor '.str_pad($number, 2, '0', STR_PAD_LEFT)]);
        }

        // Terbaru di atas: halaman 1 memuat buku 12 sampai 03, halaman 2 memuat buku 02 dan 01.
        $this->actingAs($this->petugas)->get('/books')
            ->assertSee('Buku Nomor 12')
            ->assertSee('Buku Nomor 03')
            ->assertDontSee('Buku Nomor 02')
            ->assertSee('page=2', false);

        $this->actingAs($this->petugas)->get('/books?page=2')
            ->assertSee('Buku Nomor 02')
            ->assertSee('Buku Nomor 01')
            ->assertDontSee('Buku Nomor 12');
    }

    public function test_index_does_not_run_extra_queries_per_book(): void
    {
        $countQueries = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($this->petugas)->get('/books')->assertOk();

            return count(DB::getQueryLog());
        };

        foreach (range(1, 2) as $i) {
            $this->createBook();
        }
        $few = $countQueries();

        foreach (range(1, 6) as $i) {
            $this->createBook();
        }

        $this->assertSame($few, $countQueries());
    }

    /*
    |--------------------------------------------------------------------------
    | Tambah buku
    |--------------------------------------------------------------------------
    */

    public function test_create_form_lists_reference_data_but_only_real_publishers(): void
    {
        Category::factory()->create(['name' => 'Sejarah']);
        Publisher::factory()->publisher()->create(['name' => 'Balai Pustaka']);
        Publisher::factory()->distributor()->create(['name' => 'CV Pemasok Buku']);
        Author::factory()->create(['name' => 'Pramoedya Ananta Toer']);

        $this->actingAs($this->petugas)->get('/books/create')
            ->assertOk()
            ->assertSee('Sejarah')
            ->assertSee('Balai Pustaka')
            ->assertSee('Pramoedya Ananta Toer')
            ->assertDontSee('CV Pemasok Buku')
            ->assertDontSee('belum tersedia, sehingga buku belum dapat disimpan');
    }

    public function test_create_form_explains_when_reference_data_is_missing(): void
    {
        $this->actingAs($this->petugas)->get('/books/create')
            ->assertOk()
            ->assertSee('Data penerbit belum tersedia, sehingga buku belum dapat disimpan.');
    }

    public function test_book_can_be_created_with_direct_category_and_author_names(): void
    {
        $publisher = Publisher::factory()->publisher()->create();

        $response = $this->actingAs($this->petugas)->post('/books', [
            'title' => 'Bumi Manusia',
            'category_name' => 'Sastra Indonesia',
            'publisher_id' => $publisher->id,
            'author_names' => 'Pramoedya Ananta Toer, Joesoef Isak',
            'publication_year' => 2005,
            'stock' => 5,
            'status' => 'available',
        ]);

        $book = Book::firstOrFail();
        $response->assertRedirect(route('books.show', $book))
            ->assertSessionHas('success', 'Buku "Bumi Manusia" berhasil ditambahkan.');

        $this->assertSame('Sastra Indonesia', $book->category->name);
        $this->assertEqualsCanonicalizing(['Pramoedya Ananta Toer', 'Joesoef Isak'], $book->authors->pluck('name')->all());
    }

    public function test_book_can_be_created_with_authors(): void
    {
        $payload = $this->validPayload(['title' => 'Bumi Manusia']);

        $response = $this->actingAs($this->petugas)->post('/books', $payload);

        $book = Book::firstOrFail();
        $response->assertRedirect(route('books.show', $book))
            ->assertSessionHas('success', 'Buku "Bumi Manusia" berhasil ditambahkan.');

        $this->assertSame('Bumi Manusia', $book->title);
        $this->assertSame($payload['category_id'], $book->category_id);
        $this->assertSame($payload['publisher_id'], $book->publisher_id);
        $this->assertSame(2005, $book->publication_year);
        $this->assertSame(5, $book->stock);
        $this->assertSame('available', $book->status);
        // Relasi many-to-many tersimpan di tabel pivot book_authors.
        $this->assertEqualsCanonicalizing($payload['authors'], $book->authors->pluck('id')->all());
        $this->assertDatabaseCount('book_authors', 2);
    }

    public function test_book_can_be_created_without_authors_and_isbn(): void
    {
        $this->actingAs($this->petugas)->post('/books', $this->validPayload(['authors' => [], 'isbn' => '', 'description' => '']))
            ->assertSessionHasNoErrors();

        $book = Book::firstOrFail();
        $this->assertNull($book->isbn);
        $this->assertNull($book->description);
        $this->assertCount(0, $book->authors);
    }

    public function test_invalid_data_is_rejected_with_indonesian_messages(): void
    {
        $response = $this->actingAs($this->petugas)->from('/books/create')->post('/books', [
            'title' => '',
            'isbn' => str_repeat('9', 21),
            'category_id' => 999,
            'publisher_id' => 999,
            'authors' => [999],
            'publication_year' => 'abc',
            'stock' => -1,
            'status' => 'hilang',
        ]);

        $response->assertRedirect('/books/create')->assertSessionHasErrors([
            'title' => 'Judul wajib diisi.',
            'isbn' => 'ISBN tidak boleh lebih dari 20 karakter.',
            'category_id' => 'Kategori yang dipilih tidak valid.',
            'publisher_id' => 'Penerbit yang dipilih tidak valid.',
            'authors.0' => 'Pengarang yang dipilih tidak valid.',
            'publication_year' => 'Tahun terbit harus berupa bilangan bulat.',
            'stock' => 'Stok harus bernilai minimal 0.',
            'status' => 'Status yang dipilih tidak valid.',
        ]);

        $this->assertDatabaseCount('books', 0);
    }

    public function test_publication_year_must_be_realistic(): void
    {
        $this->actingAs($this->petugas)->post('/books', $this->validPayload(['publication_year' => 999]))
            ->assertSessionHasErrors('publication_year');

        $this->actingAs($this->petugas)->post('/books', $this->validPayload(['publication_year' => now()->year + 5]))
            ->assertSessionHasErrors('publication_year');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_failed_validation_keeps_previous_input(): void
    {
        $this->actingAs($this->petugas)
            ->followingRedirects()
            ->from('/books/create')
            ->post('/books', $this->validPayload(['title' => 'Judul Yang Diketik', 'stock' => -5]))
            ->assertSee('value="Judul Yang Diketik"', false)
            ->assertSee('Stok harus bernilai minimal 0.');
    }

    public function test_isbn_must_be_unique(): void
    {
        $this->createBook(['isbn' => '9786020000001']);

        $this->actingAs($this->petugas)->post('/books', $this->validPayload(['isbn' => '9786020000001']))
            ->assertSessionHasErrors(['isbn' => 'ISBN sudah digunakan.']);

        $this->assertDatabaseCount('books', 1);
    }

    public function test_a_distributor_cannot_be_chosen_as_book_publisher(): void
    {
        $distributor = Publisher::factory()->distributor()->create();

        $this->actingAs($this->petugas)->post('/books', $this->validPayload(['publisher_id' => $distributor->id]))
            ->assertSessionHasErrors(['publisher_id' => 'Penerbit yang dipilih tidak valid.']);

        $this->assertDatabaseCount('books', 0);
    }

    public function test_unknown_fields_cannot_be_mass_assigned(): void
    {
        $this->actingAs($this->petugas)->post('/books', $this->validPayload(['id' => 500, 'cover_image' => 'x.jpg', 'created_at' => '2000-01-01']));

        $book = Book::firstOrFail();
        $this->assertNotSame(500, $book->id);
        $this->assertNull($book->cover_image);
        $this->assertSame(now()->year, $book->created_at->year);
    }

    /*
    |--------------------------------------------------------------------------
    | Detail buku
    |--------------------------------------------------------------------------
    */

    public function test_show_displays_book_details(): void
    {
        $book = $this->createBook([
            'title' => 'Ronggeng Dukuh Paruk', 'isbn' => '9789792201000', 'description' => "Novel klasik.\nTrilogi pertama.",
            'publication_year' => 1982, 'stock' => 3,
        ], category: 'Fiksi', publisher: 'Gramedia', authors: ['Ahmad Tohari']);

        $this->actingAs($this->petugas)->get("/books/{$book->id}")
            ->assertOk()
            ->assertSee('Ronggeng Dukuh Paruk')
            ->assertSee('9789792201000')
            ->assertSee('Fiksi')
            ->assertSee('Gramedia')
            ->assertSee('Ahmad Tohari')
            ->assertSee('1982')
            ->assertSee('3 eksemplar')
            ->assertSee('Tersedia')
            ->assertSee('Trilogi pertama.')
            ->assertSee(route('books.index'))
            ->assertSee(route('books.edit', $book));
    }

    public function test_show_handles_book_without_optional_data(): void
    {
        $book = $this->createBook(['isbn' => null, 'description' => null], authors: []);

        $this->actingAs($this->petugas)->get("/books/{$book->id}")->assertOk();
    }

    public function test_missing_book_returns_404(): void
    {
        $this->actingAs($this->petugas)->get('/books/999')->assertNotFound();
        $this->actingAs($this->petugas)->get('/books/999/edit')->assertNotFound();
        $this->actingAs($this->petugas)->put('/books/999', $this->validPayload())->assertNotFound();
        $this->actingAs($this->petugas)->delete('/books/999')->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | Edit buku
    |--------------------------------------------------------------------------
    */

    public function test_edit_form_is_prefilled_with_the_selected_relations(): void
    {
        $book = $this->createBook(['title' => 'Judul Lama', 'isbn' => '9780000000011'], category: 'Sains', publisher: 'Erlangga', authors: ['Penulis Terpilih']);
        Author::factory()->create(['name' => 'Penulis Lain']);

        $response = $this->actingAs($this->petugas)->get("/books/{$book->id}/edit")
            ->assertOk()
            ->assertSee('value="Judul Lama"', false)
            ->assertSee('value="9780000000011"', false)
            ->assertSee('value="Sains"', false)
            ->assertSee('value="Penulis Terpilih"', false);

        preg_match('/<option value="'.$book->publisher_id.'"[^>]*>/', $response->getContent(), $publisher);
        $this->assertMatchesRegularExpression('/\sselected\b/', $publisher[0]);
    }

    public function test_book_can_be_updated_and_changes_are_saved(): void
    {
        [$keep, $remove, $add] = Author::factory()->count(3)->create();
        $book = $this->createBook(['title' => 'Lama', 'stock' => 1]);
        $book->authors()->sync([$keep->id, $remove->id]);
        $newCategory = Category::factory()->create();

        $response = $this->actingAs($this->petugas)->put("/books/{$book->id}", $this->validPayload([
            'title' => 'Judul Baru',
            'category_id' => $newCategory->id,
            'publisher_id' => $book->publisher_id,
            'stock' => 9,
            'status' => 'unavailable',
            'authors' => [$keep->id, $add->id],
        ]));

        $response->assertRedirect(route('books.show', $book))
            ->assertSessionHas('success', 'Buku "Judul Baru" berhasil diperbarui.');

        $book->refresh();
        $this->assertSame('Judul Baru', $book->title);
        $this->assertSame($newCategory->id, $book->category_id);
        $this->assertSame(9, $book->stock);
        $this->assertSame('unavailable', $book->status);
        $this->assertEqualsCanonicalizing([$keep->id, $add->id], $book->authors->pluck('id')->all());
        $this->assertModelExists($remove); // penulis yang dilepas tidak ikut terhapus
    }

    public function test_unchecking_all_authors_removes_them_from_the_book(): void
    {
        $book = $this->createBook(authors: ['Penulis A', 'Penulis B']);
        $payload = $this->validPayload([
            'category_id' => $book->category_id, 'publisher_id' => $book->publisher_id,
        ]);
        // Form tanpa centang tidak mengirim field 'authors' sama sekali (bukan array kosong).
        unset($payload['authors']);
        $authorsBefore = Author::count();

        $this->actingAs($this->petugas)->put("/books/{$book->id}", $payload);

        $this->assertCount(0, $book->fresh()->authors);
        $this->assertSame($authorsBefore, Author::count()); // penulis hanya dilepas, tidak dihapus
    }

    public function test_isbn_uniqueness_ignores_the_book_being_edited(): void
    {
        $book = $this->createBook(['isbn' => '9781111111111']);
        $this->createBook(['isbn' => '9782222222222']);

        $payload = $this->validPayload(['category_id' => $book->category_id, 'publisher_id' => $book->publisher_id]);

        $this->actingAs($this->petugas)->put("/books/{$book->id}", [...$payload, 'isbn' => '9781111111111'])
            ->assertSessionHasNoErrors();

        $this->actingAs($this->petugas)->put("/books/{$book->id}", [...$payload, 'isbn' => '9782222222222'])
            ->assertSessionHasErrors(['isbn' => 'ISBN sudah digunakan.']);
    }

    public function test_invalid_update_does_not_change_the_book(): void
    {
        $book = $this->createBook(['title' => 'Judul Asli']);

        $this->actingAs($this->petugas)->put("/books/{$book->id}", $this->validPayload(['title' => '', 'stock' => -1]))
            ->assertSessionHasErrors(['title', 'stock']);

        $this->assertSame('Judul Asli', $book->fresh()->title);
    }

    /*
    |--------------------------------------------------------------------------
    | Hapus buku
    |--------------------------------------------------------------------------
    */

    public function test_book_can_be_deleted_without_deleting_related_data(): void
    {
        $book = $this->createBook(['title' => 'Akan Dihapus'], authors: ['Penulis A', 'Penulis B']);
        $other = $this->createBook(authors: []);

        $this->actingAs($this->petugas)->delete("/books/{$book->id}")
            ->assertRedirect(route('books.index'))
            ->assertSessionHas('success', 'Buku "Akan Dihapus" berhasil dihapus.');

        $this->assertModelMissing($book);
        $this->assertModelExists($other);
        $this->assertDatabaseMissing('book_authors', ['book_id' => $book->id]);
        // Kategori, penerbit, dan penulis tetap ada.
        $this->assertModelExists($book->category);
        $this->assertModelExists($book->publisher);
        $this->assertSame(2, Author::count());
    }

    public function test_admin_can_also_delete_books(): void
    {
        $book = $this->createBook();

        $this->actingAs(User::factory()->admin()->create())->delete("/books/{$book->id}");

        $this->assertModelMissing($book);
    }

    public function test_pages_have_a_delete_confirmation_using_the_delete_method(): void
    {
        $book = $this->createBook(['title' => 'Buku "Berkutip"']);

        foreach (['/books', "/books/{$book->id}"] as $uri) {
            $this->actingAs($this->petugas)->get($uri)
                ->assertDontSee('confirm(', false)
                ->assertDontSee('onsubmit=', false)
                ->assertSee('role="dialog"', false)
                ->assertSee('name="_method" value="DELETE"', false)
                ->assertSee('name="_token"', false)
                ->assertSee('data-action="'.route('books.destroy', $book).'"', false)
                ->assertSee('data-title="Hapus Buku?"', false)
                // Judul berisi tanda kutip tidak boleh merusak atribut HTML maupun JavaScript.
                ->assertSee('data-name="Buku &quot;Berkutip&quot;"', false);
        }
    }

    public function test_database_delete_failure_shows_a_friendly_message(): void
    {
        $book = $this->createBook(['title' => 'Masih Dipakai']);

        // Simulasi constraint database (mis. tabel lain yang mereferensikan buku) menolak penghapusan.
        Book::deleting(fn () => throw new QueryException(
            'sqlite', 'delete from books where id = ?', [$book->id], new PDOException('FOREIGN KEY constraint failed'),
        ));

        $this->actingAs($this->petugas)->delete("/books/{$book->id}")
            ->assertSessionHas('error', 'Buku "Masih Dipakai" tidak dapat dihapus karena masih digunakan oleh data lain.');

        $this->assertModelExists($book);
    }

    /**
     * Membuat buku lengkap dengan kategori, penerbit (bertipe publisher), dan penulis.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<int, string>|null  $authors  nama penulis; null = 2 penulis acak
     */
    private function createBook(array $attributes = [], ?string $category = null, ?string $publisher = null, ?array $authors = null): Book
    {
        $book = Book::factory()->create([
            'category_id' => Category::factory()->create($category ? ['name' => $category] : []),
            'publisher_id' => Publisher::factory()->publisher()->create($publisher ? ['name' => $publisher] : []),
            ...$attributes,
        ]);

        $authorIds = $authors === null
            ? Author::factory()->count(2)->create()->pluck('id')
            : collect($authors)->map(fn (string $name) => Author::factory()->create(['name' => $name])->id);

        $book->authors()->attach($authorIds);

        return $book->load(['category', 'publisher', 'authors']);
    }

    /**
     * Data form yang valid; kategori, penerbit, dan 2 penulis dibuat otomatis.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Laskar Pelangi',
            'isbn' => null,
            'description' => 'Kisah sepuluh anak di Belitung.',
            'category_id' => Category::factory()->create()->id,
            'publisher_id' => Publisher::factory()->publisher()->create()->id,
            'authors' => Author::factory()->count(2)->create()->pluck('id')->all(),
            'publication_year' => 2005,
            'stock' => 5,
            'status' => 'available',
        ], $overrides);
    }
}
