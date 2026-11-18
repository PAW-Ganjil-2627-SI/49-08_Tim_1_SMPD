{{--
    Form bersama tambah & edit buku.
    Variabel: $book, $authorNames, $categories, $publishers, $authors, $statuses, $action, $method, $submitLabel
--}}
@php
    $input = 'mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-700 focus:ring-blue-700';
    $missing = collect(['penerbit' => $publishers->isEmpty()])->filter()->keys();
    $categoryValue = old('category_name', $book->category?->name);
    $authorsValue = old('author_names', $authorNames ?? ($book->exists ? $book->authors->pluck('name')->join(', ') : ''));
@endphp

@if ($missing->isNotEmpty())
    <div class="mb-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Data {{ $missing->join(', ', ' dan ') }} belum tersedia, sehingga buku belum dapat disimpan.
        Data referensi ini perlu ada terlebih dahulu.
        (Untuk development: <code>php artisan db:seed --class=BookReferenceSeeder</code>)
    </div>
@endif

<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-5 rounded-lg border border-slate-200 bg-white p-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <x-input-label for="title" value="Judul *" />
        <input id="title" name="title" type="text" value="{{ old('title', $book->title) }}" required maxlength="255" class="{{ $input }}">
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <x-input-label for="isbn" value="ISBN" />
            <input id="isbn" name="isbn" type="text" value="{{ old('isbn', $book->isbn) }}" maxlength="20" class="{{ $input }}">
            <x-input-error :messages="$errors->get('isbn')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="publication_year" value="Tahun terbit *" />
            <input id="publication_year" name="publication_year" type="number" value="{{ old('publication_year', $book->publication_year) }}" required class="{{ $input }}">
            <x-input-error :messages="$errors->get('publication_year')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="category_name" value="Kategori *" />
            <input
                id="category_name"
                name="category_name"
                type="text"
                list="category-options"
                value="{{ $categoryValue }}"
                required
                maxlength="255"
                placeholder="Ketik atau pilih kategori"
                class="{{ $input }}"
            >
            <datalist id="category-options">
                @foreach ($categories as $category)
                    <option value="{{ $category->name }}"></option>
                @endforeach
            </datalist>
            <x-input-error :messages="collect($errors->get('category_name'))->merge($errors->get('category_id'))->unique()->all()" class="mt-2" />
        </div>

        <div>
            <x-input-label for="publisher_id" value="Penerbit *" />
            <select id="publisher_id" name="publisher_id" required class="{{ $input }}">
                <option value="">Pilih penerbit</option>
                @foreach ($publishers as $publisher)
                    <option value="{{ $publisher->id }}" @selected((int) old('publisher_id', $book->publisher_id) === $publisher->id)>{{ $publisher->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('publisher_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="stock" value="Stok *" />
            <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $book->stock) }}" required class="{{ $input }}">
            <x-input-error :messages="$errors->get('stock')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="status" value="Status *" />
            <select id="status" name="status" required class="{{ $input }}">
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $book->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="author_names" value="Pengarang" />
        <input
            id="author_names"
            name="author_names"
            type="text"
            list="author-options"
            value="{{ $authorsValue }}"
            maxlength="500"
            placeholder="Contoh: Andrea Hirata, Pramoedya Ananta Toer"
            class="{{ $input }}"
        >
        <datalist id="author-options">
            @foreach ($authors as $author)
                <option value="{{ $author->name }}"></option>
            @endforeach
        </datalist>
        <p class="mt-1 text-xs text-slate-500">Pisahkan dengan tanda koma (,) jika pengarang lebih dari satu.</p>
        <x-input-error :messages="collect($errors->get('author_names'))->merge($errors->get('authors'))->merge(collect($errors->get('authors.*'))->flatten())->unique()->all()" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" value="Deskripsi" />
        <textarea id="description" name="description" rows="4" class="{{ $input }}">{{ old('description', $book->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-5">
        <button type="submit" @disabled($missing->isNotEmpty())
            class="inline-flex items-center rounded-md bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50">
            {{ $submitLabel }}
        </button>
        <a href="{{ $book->exists ? route('books.show', $book) : route('books.index') }}"
            class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Kembali
        </a>
    </div>
</form>
