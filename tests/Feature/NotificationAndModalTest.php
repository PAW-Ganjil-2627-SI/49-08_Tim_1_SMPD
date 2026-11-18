<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Modal konfirmasi hapus kustom, notifikasi sesi, dan istilah "Pengarang" di antarmuka.
 */
class NotificationAndModalTest extends TestCase
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
    | Modal konfirmasi hapus
    |--------------------------------------------------------------------------
    */

    public function test_modal_is_accessible_and_present_on_every_app_page(): void
    {
        foreach (['/dashboard', '/books'] as $uri) {
            $this->actingAs($this->petugas)->get($uri)
                ->assertSee('role="dialog"', false)
                ->assertSee('aria-modal="true"', false)
                ->assertSee('aria-labelledby="confirm-delete-title"', false)
                ->assertSee('id="confirm-delete-title"', false)
                ->assertSee('Batal')
                ->assertSee('name="_method" value="DELETE"', false)
                ->assertSee('name="_token"', false);
        }
    }

    public function test_cancel_button_only_closes_the_modal_and_never_submits(): void
    {
        $html = $this->actingAs($this->petugas)->get('/books')->getContent();

        // Tombol Batal bertipe "button" dan hanya mengirim event close-modal.
        $this->assertMatchesRegularExpression(
            '/<button type="button" x-on:click="\$dispatch\(\'close-modal\', \'confirm-delete\'\)"[^>]*>\s*Batal\s*<\/button>/',
            $html,
        );
        // Latar (overlay) hanya menutup modal; tidak ada kode yang mengirim form dari overlay.
        $this->assertStringContainsString('x-on:click="show = false"', $html);
    }

    public function test_opening_a_page_or_a_delete_trigger_does_not_delete_anything(): void
    {
        $book = $this->createBook();

        foreach (['/books', "/books/{$book->id}"] as $uri) {
            $this->actingAs($this->petugas)->get($uri)->assertOk();
        }

        // Tombol pemicu berupa <button type="button">, bukan tombol submit.
        $this->actingAs($this->petugas)->get('/books')
            ->assertSee('<button
    type="button"
    data-action="'.route('books.destroy', $book).'"', false);

        $this->assertModelExists($book);
    }

    public function test_trigger_buttons_carry_title_and_item_name_for_each_module(): void
    {
        $book = $this->createBook(['title' => 'Laskar Pelangi']);

        $this->actingAs($this->petugas)->get('/books')
            ->assertSee('data-title="Hapus Buku?"', false)
            ->assertSee('data-name="Laskar Pelangi"', false)
            ->assertSee('data-action="'.route('books.destroy', $book).'"', false);

        $this->actingAs($this->petugas)->get("/books/{$book->id}")
            ->assertSee('data-title="Hapus Buku?"', false)
            ->assertSee('data-name="Laskar Pelangi"', false);
    }

    public function test_no_browser_dialog_is_used_in_views_or_scripts(): void
    {
        $files = array_merge(
            glob(resource_path('views/**/*.blade.php')) ?: [],
            glob(resource_path('views/*/*/*.blade.php')) ?: [],
            glob(resource_path('views/*/*.blade.php')) ?: [],
            glob(resource_path('js/*.js')) ?: [],
        );
        $this->assertNotEmpty($files);

        foreach (array_unique($files) as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/(?<![\w.$-])(alert|confirm|prompt)\s*\(/',
                file_get_contents($file),
                "{$file} masih memakai dialog bawaan browser.",
            );
        }
    }

    public function test_deleting_still_requires_login(): void
    {
        $book = $this->createBook();

        $this->delete("/books/{$book->id}")->assertRedirect(route('login', absolute: false));

        $this->assertModelExists($book);
    }

    public function test_confirming_deletion_sends_a_delete_request_that_removes_the_record(): void
    {
        $book = $this->createBook(['title' => 'Akan Hilang']);

        $this->actingAs($this->petugas)->delete("/books/{$book->id}")
            ->assertRedirect(route('books.index'));

        $this->assertModelMissing($book);
    }

    /*
    |--------------------------------------------------------------------------
    | Notifikasi
    |--------------------------------------------------------------------------
    */

    public function test_each_notification_type_is_rendered_with_its_own_style(): void
    {
        $styles = [
            'success' => 'border-green-200 bg-green-50',
            'error' => 'border-red-200 bg-red-50',
            'warning' => 'border-amber-200 bg-amber-50',
            'info' => 'border-blue-200 bg-blue-50',
        ];

        foreach ($styles as $type => $class) {
            $this->actingAs($this->petugas)->withSession([$type => "Pesan {$type} uji."])->get('/dashboard')
                ->assertSee("Pesan {$type} uji.")
                ->assertSee('data-flash="'.$type.'"', false)
                ->assertSee($class, false)
                ->assertSee('Tutup notifikasi');
        }
    }

    public function test_only_success_notifications_hide_themselves(): void
    {
        $this->actingAs($this->petugas)->withSession(['success' => 'Berhasil.'])->get('/dashboard')
            ->assertSee('setTimeout(() => show = false, 6000)', false);

        foreach (['error', 'warning', 'info'] as $type) {
            // withSession() menyimpan data langsung ke sesi, jadi bersihkan dulu pesan sebelumnya.
            $this->flushSession();
            $this->actingAs($this->petugas)->withSession([$type => 'Pesan.'])->get('/dashboard')
                ->assertDontSee('setTimeout(() => show = false, 6000)', false);
        }
    }

    public function test_no_notification_is_rendered_without_a_session_message(): void
    {
        $this->actingAs($this->petugas)->get('/dashboard')
            ->assertDontSee('data-flash=', false);
    }

    public function test_notifications_escape_html(): void
    {
        $this->actingAs($this->petugas)->withSession(['error' => '<script>alert(1)</script>'])->get('/dashboard')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_success_notification_is_shown_after_adding_and_editing(): void
    {
        $payload = $this->validPayload();
        $payload['title'] = 'Bumi Manusia';

        $this->actingAs($this->petugas)->from('/books/create')->followingRedirects()
            ->post('/books', $payload)
            ->assertSee('data-flash="success"', false)
            ->assertSee('Buku &quot;Bumi Manusia&quot; berhasil ditambahkan.', false);

        $book = Book::firstWhere('title', 'Bumi Manusia');
        $payload['title'] = 'Anak Semua Bangsa';

        $this->actingAs($this->petugas)->followingRedirects()
            ->put("/books/{$book->id}", $payload)
            ->assertSee('data-flash="success"', false)
            ->assertSee('Buku &quot;Anak Semua Bangsa&quot; berhasil diperbarui.', false);
    }

    public function test_success_notification_is_shown_after_deleting(): void
    {
        $book = $this->createBook(['title' => 'Sementara']);

        $this->actingAs($this->petugas)->followingRedirects()
            ->delete("/books/{$book->id}")
            ->assertSee('data-flash="success"', false)
            ->assertSee('Buku &quot;Sementara&quot; berhasil dihapus.', false);
    }

    public function test_validation_errors_stay_next_to_their_fields(): void
    {
        $this->actingAs($this->petugas)->from('/books/create')->followingRedirects()
            ->post('/books', ['title' => ''])
            ->assertSee('Judul wajib diisi.')
            ->assertDontSee('data-flash=', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Istilah "Pengarang"
    |--------------------------------------------------------------------------
    */

    public function test_book_pages_use_the_term_pengarang_instead_of_penulis(): void
    {
        $book = $this->createBook(['title' => 'Buku Uji'], authors: ['Andrea Hirata']);

        $pages = [
            'index' => $this->actingAs($this->petugas)->get('/books'),
            'show' => $this->actingAs($this->petugas)->get("/books/{$book->id}"),
            'create' => $this->actingAs($this->petugas)->get('/books/create'),
            'edit' => $this->actingAs($this->petugas)->get("/books/{$book->id}/edit"),
        ];

        foreach ($pages as $name => $response) {
            $response->assertSee('Pengarang');
            $this->assertStringNotContainsStringIgnoringCase('penulis', $response->getContent(), "Halaman buku {$name} masih memakai istilah Penulis.");
        }

        $pages['index']->assertSee('Andrea Hirata');
        $pages['show']->assertSee('Andrea Hirata');
    }

    public function test_validation_messages_use_pengarang_for_the_book_authors_field(): void
    {
        $payload = $this->validPayload();
        $payload['authors'] = [999999];

        $this->actingAs($this->petugas)->post('/books', $payload)
            ->assertSessionHasErrors(['authors.0' => 'Pengarang yang dipilih tidak valid.']);

        $this->assertSame('pengarang', trans('validation.attributes.authors'));
        $this->assertSame('pengarang', trans('validation.attributes')['authors.*']);
    }

    public function test_navigation_description_uses_pengarang(): void
    {
        $this->assertStringContainsString('pengarang', config('navigation.groups.0.items.1.description') ?? json_encode(config('navigation')));
        $this->assertStringNotContainsStringIgnoringCase('penulis', json_encode(config('navigation')));
    }

    public function test_internal_names_and_relations_are_unchanged(): void
    {
        $book = $this->createBook(authors: ['A', 'B']);

        $this->assertCount(2, $book->authors);
        $this->assertSame('book_authors', $book->authors()->getTable());
        $this->assertSame('authors', (new Author)->getTable());
        $this->assertFalse(Route::has('authors.index'));
        $this->assertFalse(Route::has('categories.index'));
        $this->assertTrue(Schema::hasTable('book_authors'));
        $this->assertTrue(Schema::hasColumn('book_authors', 'author_id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<int, string>  $authors
     */
    private function createBook(array $attributes = [], array $authors = ['Pengarang Uji']): Book
    {
        $book = Book::factory()->create($attributes + [
            'category_id' => Category::factory()->create()->id,
            'publisher_id' => Publisher::factory()->publisher()->create()->id,
        ]);

        $book->authors()->sync(collect($authors)->map(fn ($name) => Author::firstOrCreate(['name' => $name])->id)->all());

        return $book;
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'title' => 'Buku Baru',
            'category_id' => Category::factory()->create()->id,
            'publisher_id' => Publisher::factory()->publisher()->create()->id,
            'publication_year' => 2020,
            'stock' => 3,
            'status' => 'available',
        ];
    }
}
