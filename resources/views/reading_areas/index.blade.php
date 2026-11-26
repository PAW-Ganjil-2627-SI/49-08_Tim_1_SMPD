@extends('layouts.app')

@section('title', 'Area Baca - Fasilitas & Area')

@section('content')
    <x-page-header
        title="Fasilitas & Area"
        description="Kelola area baca, fasilitas perpustakaan, dan pemetaan hubungan antara keduanya."
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Fasilitas & Area' => null]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('reading-areas.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    <span>Tambah Area Baca</span>
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Sub-tab Navigasi -->
    <x-facility-area-tabs active="areas" />

    <!-- Ringkasan Statistik -->
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Statistik Area Baca">
        <x-stat-card
            label="Total Area Baca"
            :value="$stats['total_areas']"
            icon="map"
            hint="Ruang & bilik baca terdaftar"
        />
        <x-stat-card
            label="Area Tersedia"
            :value="$stats['available_areas']"
            icon="check-circle"
            hint="Dapat digunakan pemustaka"
        />
        <x-stat-card
            label="Total Kapasitas"
            :value="$stats['total_capacity']"
            icon="users"
            hint="Daya tampung total pemustaka (orang)"
        />
        <x-stat-card
            label="Pilihan Fasilitas"
            :value="$stats['total_facilities']"
            icon="building-office"
            hint="Fasilitas yang dapat dihubungkan"
        />
    </section>

    <!-- Filter & Pencarian Sederhana -->
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('reading-areas.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <label for="search" class="sr-only">Cari area baca</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Cari nama area atau deskripsi..."
                        class="w-full rounded-lg border-slate-300 pl-9 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-blue-600"
                    />
                </div>
            </div>

            <div class="lg:col-span-2">
                <label for="floor_number" class="sr-only">Filter Lantai</label>
                <select
                    name="floor_number"
                    id="floor_number"
                    class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-600 focus:ring-blue-600"
                >
                    <option value="">Semua Lantai</option>
                    @foreach ($existingFloors as $floor)
                        <option value="{{ $floor }}" @selected(($filters['floor_number'] ?? '') !== '' && ($filters['floor_number'] ?? null) == $floor)>
                            Lantai {{ $floor }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label for="facility_id" class="sr-only">Filter Fasilitas</label>
                <select
                    name="facility_id"
                    id="facility_id"
                    class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-600 focus:ring-blue-600"
                >
                    <option value="">Semua Fasilitas</option>
                    @foreach ($availableFacilities as $fac)
                        <option value="{{ $fac->id }}" @selected(($filters['facility_id'] ?? '') == $fac->id)>
                            {{ $fac->name }} ({{ $fac->type }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="status" class="sr-only">Status</label>
                <select
                    name="status"
                    id="status"
                    class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-600 focus:ring-blue-600"
                >
                    <option value="">Semua Status</option>
                    <option value="available" @selected(($filters['status'] ?? '') === 'available')>Tersedia</option>
                    <option value="unavailable" @selected(($filters['status'] ?? '') === 'unavailable')>Tidak Tersedia</option>
                </select>
            </div>

            <div class="flex items-center gap-2 lg:col-span-1">
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-700"
                    title="Terapkan Filter"
                >
                    <x-icon name="funnel" class="h-4 w-4" />
                </button>
                @if (array_filter($filters))
                    <a
                        href="{{ route('reading-areas.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white p-2 text-sm text-slate-600 hover:bg-slate-50"
                        title="Reset Filter"
                    >
                        <x-icon name="x-mark" class="h-4 w-4" />
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Area Baca -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-3.5">Area Baca</th>
                        <th scope="col" class="px-6 py-3.5">Lokasi & Kapasitas</th>
                        <th scope="col" class="px-6 py-3.5">Fasilitas yang Terhubung</th>
                        <th scope="col" class="px-6 py-3.5">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($readingAreas as $area)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('reading-areas.show', $area) }}" class="font-semibold text-blue-900 hover:underline">
                                    {{ $area->name }}
                                </a>
                                @if ($area->description)
                                    <p class="mt-0.5 text-xs text-slate-500 line-clamp-1">{{ $area->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800">Lantai {{ $area->floor_number }}</div>
                                <div class="text-xs text-slate-500">{{ $area->capacity }} Orang</div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($area->facilities->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5 max-w-md">
                                        @foreach ($area->facilities as $facility)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 border border-blue-100">
                                                <x-icon name="check-circle" class="h-3 w-3 text-blue-500" />
                                                {{ $facility->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs italic text-slate-400">Belum ada fasilitas dihubungkan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($area->status === 'available')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                        Tidak Tersedia
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a
                                        href="{{ route('reading-areas.show', $area) }}"
                                        class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-blue-900"
                                        title="Lihat Detail Area"
                                    >
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                    <a
                                        href="{{ route('reading-areas.edit', $area) }}"
                                        class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-amber-700"
                                        title="Edit Area Baca"
                                    >
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form
                                        action="{{ route('reading-areas.destroy', $area) }}"
                                        method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus area baca \'{{ $area->name }}\'?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-lg p-1.5 text-slate-500 hover:bg-rose-50 hover:text-rose-700"
                                            title="Hapus Area Baca"
                                        >
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                    <x-icon name="map" class="h-6 w-6" />
                                </div>
                                <h3 class="mt-3 text-sm font-semibold text-slate-800">Tidak ada area baca</h3>
                                <p class="mt-1 text-sm text-slate-500">Belum ada data area baca yang sesuai dengan kriteria pencarian.</p>
                                <div class="mt-4">
                                    <a
                                        href="{{ route('reading-areas.create') }}"
                                        class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800"
                                    >
                                        <x-icon name="plus" class="h-4 w-4" />
                                        <span>Tambah Area Baca</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($readingAreas->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $readingAreas->links() }}
            </div>
        @endif
    </div>
@endsection
