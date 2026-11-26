@extends('layouts.app')

@section('title', $publisher->name . ' · Penerbit & Supplier')

@section('content')
    <x-page-header
        :title="$publisher->name"
        :description="'Detail data rekanan ' . strtolower($publisher->type_label) . ' beserta kontak sales dan wilayah operasional.'"
        :breadcrumbs="[
            'Dashboard' => route('dashboard'),
            'Penerbit & Supplier' => route('publishers.index'),
            $publisher->name => null,
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('publishers.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 transition"
                >
                    <x-icon name="arrow-left" class="h-4 w-4 shrink-0" />
                    Kembali
                </a>
                <a
                    href="{{ route('publishers.edit', $publisher) }}"
                    class="inline-flex items-center gap-1.5 rounded-md bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 transition"
                >
                    <x-icon name="pencil" class="h-4 w-4 shrink-0" />
                    Ubah Data
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Header Ringkasan Profil -->
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl {{ $publisher->type === 'publisher' ? 'bg-sky-100 text-sky-700' : 'bg-emerald-100 text-emerald-700' }}">
                    <x-icon :name="$publisher->type === 'publisher' ? 'book' : 'truck'" class="h-7 w-7" />
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-900">{{ $publisher->name }}</h2>
                        @if ($publisher->type === 'publisher')
                            <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-semibold text-sky-700 border border-sky-200">
                                Penerbit
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                Distributor / Supplier
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 flex items-center gap-1 text-sm text-slate-500">
                        <x-icon name="map-pin" class="h-4 w-4 text-slate-400" />
                        {{ $publisher->region ?: 'Wilayah asal belum ditentukan' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 border-t border-slate-100 pt-3 sm:border-0 sm:pt-0">
                <div class="rounded-lg bg-slate-50 px-4 py-2 text-center border border-slate-100">
                    <span class="block text-xs font-medium text-slate-500">Koleksi Buku</span>
                    <span class="text-lg font-bold text-slate-800">{{ $publisher->books_count }} judul</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Rinci Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Kartu 1: Kontak Sales & Narahubung -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="flex items-center gap-2 text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">
                <x-icon name="user-circle" class="h-5 w-5 text-blue-700" />
                Kontak Sales & Narahubung
            </h3>
            <dl class="mt-4 space-y-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Narahubung / Sales</dt>
                    <dd class="mt-1 font-medium text-slate-800">
                        {{ $publisher->contact_person ?: '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Telepon / WhatsApp</dt>
                    <dd class="mt-1">
                        @if ($publisher->phone)
                            <a href="tel:{{ $publisher->phone }}" class="inline-flex items-center gap-1.5 font-medium text-slate-700 hover:text-blue-900 hover:underline">
                                <x-icon name="phone" class="h-4 w-4 shrink-0 text-slate-400" />
                                {{ $publisher->phone }}
                            </a>
                        @else
                            <span class="text-slate-500">-</span>
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat Email</dt>
                    <dd class="mt-1">
                        @if ($publisher->email)
                            <a href="mailto:{{ $publisher->email }}" class="inline-flex items-center gap-1.5 font-medium text-slate-700 hover:text-blue-900 hover:underline">
                                <x-icon name="envelope" class="h-4 w-4 shrink-0 text-slate-400" />
                                {{ $publisher->email }}
                            </a>
                        @else
                            <span class="text-slate-500">-</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Kartu 2: Lokasi & Alamat Lengkap -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="flex items-center gap-2 text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">
                <x-icon name="map" class="h-5 w-5 text-blue-700" />
                Alamat & Wilayah Asal
            </h3>
            <dl class="mt-4 space-y-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Wilayah Asal (Kota / Provinsi)</dt>
                    <dd class="mt-1 font-medium text-slate-800">
                        {{ $publisher->region ?: '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat Lengkap</dt>
                    <dd class="mt-1 whitespace-pre-line text-slate-700">
                        {{ $publisher->address ?: 'Belum ada alamat lengkap yang disimpan.' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Catatan Khusus</dt>
                    <dd class="mt-1 whitespace-pre-line text-slate-600 italic">
                        {{ $publisher->notes ?: 'Tidak ada catatan tambahan.' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Daftar Buku Terkait -->
    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <h3 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                <x-icon name="book" class="h-5 w-5 text-blue-700" />
                Buku yang Terhubung ({{ $publisher->books_count }})
            </h3>
            @if ($publisher->books_count > 10)
                <span class="text-xs text-slate-500">Menampilkan 10 buku terbaru</span>
            @endif
        </div>

        @if ($publisher->books->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Judul Buku</th>
                            <th scope="col" class="px-6 py-3">ISBN</th>
                            <th scope="col" class="px-6 py-3">Tahun Terbit</th>
                            <th scope="col" class="px-6 py-3">Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($publisher->books as $book)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-3.5 font-medium text-slate-900">{{ $book->title }}</td>
                                <td class="px-6 py-3.5 font-mono text-xs text-slate-600">{{ $book->isbn ?: '-' }}</td>
                                <td class="px-6 py-3.5">{{ $book->publication_year }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                        {{ $book->stock }} eksemplar
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-10 text-center">
                <p class="text-sm text-slate-500">Belum ada buku dalam katalog yang ditautkan ke penerbit ini.</p>
            </div>
        @endif
    </div>

    <!-- Bar Aksi Hapus / Kelola -->
    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="text-xs text-slate-500">
            <span>Dibuat: {{ $publisher->created_at?->translatedFormat('d F Y H:i') ?? '-' }}</span>
            <span class="mx-2">·</span>
            <span>Diperbarui: {{ $publisher->updated_at?->translatedFormat('d F Y H:i') ?? '-' }}</span>
        </div>

        <x-delete-button
            :action="route('publishers.destroy', $publisher)"
            title="Hapus Rekanan?"
            :name="$publisher->name"
            description="Data rekanan berikut akan dihapus dari sistem:"
            class="inline-flex items-center gap-1.5 rounded-md border border-red-300 bg-white px-3.5 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 hover:border-red-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 transition"
        >
            <x-icon name="trash" class="h-4 w-4 shrink-0" />
            Hapus Data Rekanan
        </x-delete-button>
    </div>
@endsection

