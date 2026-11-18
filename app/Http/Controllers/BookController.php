<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * Nilai kolom status (enum di migration) beserta labelnya.
     */
    private const STATUSES = [
        'available' => 'Tersedia',
        'unavailable' => 'Tidak tersedia',
    ];

    /**
     * Daftar buku (10 per halaman).
     */
    public function index(): View
    {
        // with() memuat relasi sekaligus agar tidak ada query tambahan untuk setiap baris.
        $books = Book::with(['category', 'publisher', 'authors'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('books.index', [
            'books' => $books,
            'statuses' => self::STATUSES,
        ]);
    }

    /**
     * Form tambah buku.
     */
    public function create(): View
    {
        return view('books.create', [
            'book' => new Book(['status' => 'available', 'stock' => 0]),
            'authorNames' => '',
            ...$this->formData(),
        ]);
    }

    /**
     * Simpan buku baru beserta kategori dan pengarangnya.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        // Buku, kategori, dan relasi pengarang disimpan bersama dalam satu transaksi.
        $book = DB::transaction(function () use ($data) {
            [$bookAttributes, $authorIds] = $this->prepareBookPayload($data);

            $book = Book::create($bookAttributes);
            $book->authors()->sync($authorIds);

            return $book;
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', "Buku \"{$book->title}\" berhasil ditambahkan.");
    }

    /**
     * Detail buku.
     */
    public function show(Book $book): View
    {
        $book->load(['category', 'publisher', 'authors']);

        return view('books.show', [
            'book' => $book,
            'statuses' => self::STATUSES,
        ]);
    }

    /**
     * Form edit buku.
     */
    public function edit(Book $book): View
    {
        $book->load(['category', 'authors']);

        return view('books.edit', [
            'book' => $book,
            'authorNames' => $book->authors->pluck('name')->join(', '),
            ...$this->formData(),
        ]);
    }

    /**
     * Perbarui buku beserta kategori dan pengarangnya.
     */
    public function update(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate($this->rules($book));

        DB::transaction(function () use ($book, $data) {
            [$bookAttributes, $authorIds] = $this->prepareBookPayload($data);

            $book->update($bookAttributes);
            // sync(): pengarang yang tidak dipilih dilepas, yang dipilih ditambahkan.
            $book->authors()->sync($authorIds);
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', "Buku \"{$book->title}\" berhasil diperbarui.");
    }

    /**
     * Hapus buku. Kategori, penerbit, dan pengarang tidak ikut terhapus.
     */
    public function destroy(Book $book): RedirectResponse
    {
        $title = $book->title;

        try {
            DB::transaction(function () use ($book) {
                $book->authors()->detach(); // hapus baris tabel pivot book_authors
                $book->delete();
            });
        } catch (QueryException $exception) {
            report($exception);

            return back()->with('error', "Buku \"{$title}\" tidak dapat dihapus karena masih digunakan oleh data lain.");
        }

        return redirect()
            ->route('books.index')
            ->with('success', "Buku \"{$title}\" berhasil dihapus.");
    }

    /**
     * Aturan validasi untuk tambah dan edit buku.
     *
     * @return array<string, mixed>
     */
    private function rules(?Book $book = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // ISBN boleh kosong, tetapi harus unik (kecuali milik buku yang sedang diedit).
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book)],
            'description' => ['nullable', 'string'],
            'category_name' => ['required_without:category_id', 'nullable', 'string', 'max:255'],
            'category_id' => ['required_without:category_name', 'nullable', 'integer', 'exists:categories,id'],
            // Hanya penerbit bertipe 'publisher'; distributor/supplier tidak boleh menjadi penerbit buku.
            'publisher_id' => ['required', 'integer', Rule::exists('publishers', 'id')->where('type', 'publisher')],
            'author_names' => ['nullable', 'string', 'max:500'],
            'authors' => ['nullable', 'array'],
            'authors.*' => ['integer', 'distinct', 'exists:authors,id'],
            'publication_year' => ['required', 'integer', 'between:1000,'.(now()->year + 1)],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ];
    }

    /**
     * Siapkan atribut buku (termasuk membuat kategori/pengarang baru dari input teks).
     *
     * @param  array<string, mixed>  $data
     * @return array{0: array<string, mixed>, 1: array<int, int>}
     */
    private function prepareBookPayload(array $data): array
    {
        $categoryId = ! empty($data['category_name'])
            ? Category::firstOrCreate(['name' => trim((string) $data['category_name'])])->id
            : (int) $data['category_id'];

        if (array_key_exists('author_names', $data)) {
            $authorIds = collect(explode(',', (string) $data['author_names']))
                ->map(fn (string $name) => trim($name))
                ->filter(fn (string $name) => $name !== '')
                ->unique()
                ->map(fn (string $name) => Author::firstOrCreate(['name' => $name])->id)
                ->values()
                ->all();
        } else {
            $authorIds = $data['authors'] ?? [];
        }

        $bookAttributes = collect($data)
            ->except(['category_name', 'author_names', 'authors'])
            ->put('category_id', $categoryId)
            ->all();

        return [$bookAttributes, $authorIds];
    }

    /**
     * Data pilihan untuk form (kategori, penerbit, pengarang, status).
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'publishers' => Publisher::where('type', 'publisher')->orderBy('name')->get(),
            'authors' => Author::orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ];
    }
}
