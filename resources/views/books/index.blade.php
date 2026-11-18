@extends('layouts.app')

@section('title', 'Katalog & Buku')

@section('content')
    <x-page-header
        title="Katalog & Buku"
        description="Daftar buku koleksi perpustakaan."
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Katalog & Buku' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('books.create') }}" class="inline-flex items-center rounded-md bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                Tambah Buku
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="relative overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">ISBN</th>
                        <th class="px-4 py-3">Pengarang</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Penerbit</th>
                        <th class="px-4 py-3">Tahun</th>
                        <th class="px-4 py-3">Stok</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($books as $book)
                        <tr class="align-top">
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <a href="{{ route('books.show', $book) }}" class="hover:text-blue-800 hover:underline">{{ $book->title }}</a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $book->isbn ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $book->authors->pluck('name')->join(', ') ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $book->category->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $book->publisher->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $book->publication_year }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $book->stock }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $statuses[$book->status] }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <a href="{{ route('books.show', $book) }}" class="text-slate-700 hover:underline">Detail</a>
                                <a href="{{ route('books.edit', $book) }}" class="ml-3 text-blue-800 hover:underline">Edit</a>
                                <x-delete-button
                                    :action="route('books.destroy', $book)"
                                    title="Hapus Buku?"
                                    description="Buku berikut akan dihapus dari katalog:"
                                    :name="$book->title"
                                    class="ml-3 text-red-600 hover:underline"
                                >Hapus</x-delete-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-slate-500">
                                Belum ada buku. Klik "Tambah Buku" untuk menambahkan buku pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $books->links() }}
    </div>
@endsection
