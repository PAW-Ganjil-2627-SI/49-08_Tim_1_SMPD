@extends('layouts.app')

@section('title', $book->title)

@section('content')
    <x-page-header
        :title="$book->title"
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Katalog & Buku' => route('books.index'), 'Detail Buku' => null]"
    />

    <div class="max-w-3xl rounded-lg border border-slate-200 bg-white p-6">
        <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase text-slate-500">Judul</dt>
                <dd class="mt-1 text-slate-900">{{ $book->title }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">ISBN</dt>
                <dd class="mt-1 text-slate-900">{{ $book->isbn ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Pengarang</dt>
                <dd class="mt-1 text-slate-900">{{ $book->authors->pluck('name')->join(', ') ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Kategori</dt>
                <dd class="mt-1 text-slate-900">{{ $book->category->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Penerbit</dt>
                <dd class="mt-1 text-slate-900">{{ $book->publisher->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Tahun Terbit</dt>
                <dd class="mt-1 text-slate-900">{{ $book->publication_year }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Stok</dt>
                <dd class="mt-1 text-slate-900">{{ $book->stock }} eksemplar</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Status</dt>
                <dd class="mt-1 text-slate-900">{{ $statuses[$book->status] }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase text-slate-500">Deskripsi</dt>
                <dd class="mt-1 whitespace-pre-line text-slate-900">{{ $book->description ?? '-' }}</dd>
            </div>
        </dl>

        <div class="mt-6 flex flex-wrap gap-3 border-t border-slate-100 pt-6">
            <a href="{{ route('books.index') }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
            <a href="{{ route('books.edit', $book) }}" class="inline-flex items-center rounded-md bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                Edit
            </a>
            <x-delete-button
                :action="route('books.destroy', $book)"
                title="Hapus Buku?"
                description="Buku berikut akan dihapus dari katalog:"
                :name="$book->title"
                class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
            >Hapus</x-delete-button>
        </div>
    </div>
@endsection
