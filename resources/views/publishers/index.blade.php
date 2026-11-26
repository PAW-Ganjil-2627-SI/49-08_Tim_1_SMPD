@extends('layouts.app')

@section('title', 'Penerbit & Supplier')

@section('content')
    <x-page-header
        title="Penerbit & Supplier"
        description="Pengelolaan data penerbit/distributor buku, kontak sales, alamat, dan wilayah asal penerbit."
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Penerbit & Supplier' => null]"
    >
        <x-slot:actions>
            <a
                href="{{ route('publishers.create') }}"
                class="inline-flex items-center gap-2 rounded-md bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 transition"
            >
                <x-icon name="plus" class="h-4 w-4 shrink-0" />
                Tambah Penerbit / Supplier
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Ringkasan Statistik -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card
            label="Total Rekanan"
            :value="$totalCount"
            icon="truck"
            hint="Total penerbit dan distributor terdaftar"
        />
        <x-stat-card
            label="Penerbit Buku"
            :value="$publisherCount"
            icon="book"
            hint="Penerbit langsung"
        />
        <x-stat-card
            label="Distributor / Supplier"
            :value="$distributorCount"
            icon="database"
            hint="Pemasok & distributor rekanan"
        />
    </div>

    <!-- Filter & Pencarian -->
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" action="{{ route('publishers.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-12 md:items-end">
            <!-- Input Cari -->
            <div class="md:col-span-6">
                <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    Cari Rekanan
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="magnifying-glass" class="h-4 w-4 shrink-0" />
                    </div>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ $search }}"
                        placeholder="Cari nama, sales, telepon, wilayah..."
                        class="block w-full rounded-md border-slate-300 pl-10 text-sm placeholder:text-slate-400 focus:border-blue-900 focus:ring-blue-900"
                    />
                </div>
            </div>

            <!-- Filter Tipe -->
            <div class="md:col-span-3">
                <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    Filter Tipe
                </label>
                <select
                    name="type"
                    id="type"
                    class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-900 focus:ring-blue-900"
                >
                    <option value="">Semua Tipe</option>
                    <option value="publisher" @selected($selectedType === 'publisher')>Penerbit</option>
                    <option value="distributor" @selected($selectedType === 'distributor')>Distributor / Supplier</option>
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center gap-2 md:col-span-3">
                <button
                    type="submit"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-md bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 transition"
                >
                    <x-icon name="magnifying-glass" class="h-4 w-4 shrink-0" />
                    Filter
                </button>

                @if ($search || $selectedType)
                    <a
                        href="{{ route('publishers.index') }}"
                        class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 transition"
                        title="Reset Filter"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th scope="col" class="px-5 py-3.5">Nama & Tipe</th>
                        <th scope="col" class="px-5 py-3.5">Kontak Sales</th>
                        <th scope="col" class="px-5 py-3.5">Wilayah Asal</th>
                        <th scope="col" class="px-5 py-3.5">Buku</th>
                        <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($publishers as $publisher)
                        <tr class="transition hover:bg-slate-50/80">
                            <!-- Nama & Tipe -->
                            <td class="px-5 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $publisher->type === 'publisher' ? 'bg-sky-100 text-sky-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        <x-icon :name="$publisher->type === 'publisher' ? 'book' : 'truck'" class="h-5 w-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('publishers.show', $publisher) }}" class="font-semibold text-slate-900 hover:text-blue-900 hover:underline">
                                            {{ $publisher->name }}
                                        </a>
                                        <div class="mt-1 flex items-center gap-2">
                                            @if ($publisher->type === 'publisher')
                                                <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700 border border-sky-200">
                                                    Penerbit
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 border border-emerald-200">
                                                    Distributor
                                                </span>
                                            @endif
                                            @if ($publisher->notes)
                                                <span class="truncate text-xs text-slate-400" title="{{ $publisher->notes }}">
                                                    · {{ Str::limit($publisher->notes, 30) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Kontak Sales -->
                            <td class="px-5 py-4">
                                @if ($publisher->contact_person || $publisher->phone || $publisher->email)
                                    <div class="space-y-1">
                                        @if ($publisher->contact_person)
                                            <p class="font-medium text-slate-800">{{ $publisher->contact_person }}</p>
                                        @endif
                                        <div class="flex flex-col gap-0.5 text-xs text-slate-500">
                                            @if ($publisher->phone)
                                                <span class="inline-flex items-center gap-1.5">
                                                    <x-icon name="phone" class="h-3.5 w-3.5 text-slate-400" />
                                                    <a href="tel:{{ $publisher->phone }}" class="hover:text-blue-900 hover:underline">{{ $publisher->phone }}</a>
                                                </span>
                                            @endif
                                            @if ($publisher->email)
                                                <span class="inline-flex items-center gap-1.5">
                                                    <x-icon name="envelope" class="h-3.5 w-3.5 text-slate-400" />
                                                    <a href="mailto:{{ $publisher->email }}" class="hover:text-blue-900 hover:underline">{{ $publisher->email }}</a>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs italic text-slate-400">Tidak ada kontak</span>
                                @endif
                            </td>

                            <!-- Wilayah Asal -->
                            <td class="px-5 py-4">
                                @if ($publisher->region)
                                    <div class="flex items-center gap-1.5 text-slate-700">
                                        <x-icon name="map-pin" class="h-4 w-4 shrink-0 text-slate-400" />
                                        <span>{{ $publisher->region }}</span>
                                    </div>
                                @else
                                    <span class="text-xs italic text-slate-400">-</span>
                                @endif
                                @if ($publisher->address)
                                    <p class="mt-1 line-clamp-1 text-xs text-slate-500" title="{{ $publisher->address }}">
                                        {{ $publisher->address }}
                                    </p>
                                @endif
                            </td>

                            <!-- Buku Terkait -->
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    <x-icon name="book" class="h-3.5 w-3.5 text-slate-500" />
                                    {{ $publisher->books_count }} buku
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <!-- Detail -->
                                    <a
                                        href="{{ route('publishers.show', $publisher) }}"
                                        class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition"
                                        title="Lihat Detail"
                                    >
                                        <x-icon name="eye" class="h-3.5 w-3.5 text-slate-500" />
                                        Detail
                                    </a>

                                    <!-- Edit -->
                                    <a
                                        href="{{ route('publishers.edit', $publisher) }}"
                                        class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition"
                                        title="Ubah Data"
                                    >
                                        <x-icon name="pencil" class="h-3.5 w-3.5 text-slate-500" />
                                        Edit
                                    </a>

                                    <!-- Hapus -->
                                    <x-delete-button
                                        :action="route('publishers.destroy', $publisher)"
                                        title="Hapus Rekanan?"
                                        :name="$publisher->name"
                                        description="Data rekanan berikut akan dihapus dari sistem:"
                                        class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition"
                                    >
                                        <x-icon name="trash" class="h-3.5 w-3.5 text-red-500" />
                                        Hapus
                                    </x-delete-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                    <x-icon name="truck" class="h-6 w-6" />
                                </div>
                                <h3 class="mt-3 text-sm font-semibold text-slate-900">Belum ada data penerbit atau distributor</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    @if ($search || $selectedType)
                                        Tidak ditemukan data yang sesuai dengan kriteria pencarian Anda.
                                    @else
                                        Mulai tambahkan data penerbit buku atau supplier pertama Anda.
                                    @endif
                                </p>
                                <div class="mt-4">
                                    @if ($search || $selectedType)
                                        <a
                                            href="{{ route('publishers.index') }}"
                                            class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50"
                                        >
                                            Reset Pencarian
                                        </a>
                                    @else
                                        <a
                                            href="{{ route('publishers.create') }}"
                                            class="inline-flex items-center gap-1.5 rounded-md bg-blue-900 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-800 transition"
                                        >
                                            <x-icon name="plus" class="h-3.5 w-3.5 shrink-0" />
                                            Tambah Data Sekarang
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($publishers->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $publishers->links() }}
            </div>
        @endif
    </div>
@endsection

