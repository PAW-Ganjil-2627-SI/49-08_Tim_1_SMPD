@extends('layouts.app')

@section('title', 'Fasilitas - Fasilitas & Area')

@section('content')
    <x-page-header
        title="Fasilitas & Area"
        description="Kelola sarana dan prasarana perpustakaan serta area baca yang menyediakannya."
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Fasilitas & Area' => null]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('facilities.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    <span>Tambah Fasilitas Baru</span>
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Sub-tab Navigasi -->
    <x-facility-area-tabs active="facilities" />

    <!-- Ringkasan Statistik -->
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Statistik Fasilitas">
        <x-stat-card
            label="Total Fasilitas"
            :value="$stats['total_facilities']"
            icon="building-office"
            hint="Sarana & prasarana terdaftar"
        />
        <x-stat-card
            label="Fasilitas Tersedia"
            :value="$stats['available_facilities']"
            icon="check-circle"
            hint="Berfungsi normal & siap pakai"
        />
        <x-stat-card
            label="Area Baca Terlayani"
            :value="$stats['total_areas']"
            icon="map"
            hint="Ruangan & bilik di perpustakaan"
        />
        <x-stat-card
            label="Jenis / Tipe"
            :value="$stats['distinct_types']"
            icon="archive"
            hint="Kategori variasi sarana"
        />
    </section>

    <!-- Filter & Pencarian Sederhana -->
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('facilities.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <label for="search" class="sr-only">Cari fasilitas</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Cari nama fasilitas, jenis, atau deskripsi..."
                        class="w-full rounded-lg border-slate-300 pl-9 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-blue-600"
                    />
                </div>
            </div>

            <div class="lg:col-span-3">
                <label for="type" class="sr-only">Jenis / Tipe</label>
                <select
                    name="type"
                    id="type"
                    class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-600 focus:ring-blue-600 capitalize"
                >
                    <option value="">Semua Jenis Fasilitas</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type) class="capitalize">
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
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
                        href="{{ route('facilities.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white p-2 text-sm text-slate-600 hover:bg-slate-50"
                        title="Reset Filter"
                    >
                        <x-icon name="x-mark" class="h-4 w-4" />
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Fasilitas -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-3.5">Fasilitas</th>
                        <th scope="col" class="px-6 py-3.5">Jenis / Tipe</th>
                        <th scope="col" class="px-6 py-3.5">Tersedia di Area Baca</th>
                        <th scope="col" class="px-6 py-3.5">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($facilities as $facility)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('facilities.show', $facility) }}" class="font-semibold text-blue-900 hover:underline">
                                    {{ $facility->name }}
                                </a>
                                @if ($facility->description)
                                    <p class="mt-0.5 text-xs text-slate-500 line-clamp-1">{{ $facility->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 capitalize border border-slate-200">
                                    {{ $facility->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($facility->readingAreas->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5 max-w-md">
                                        @foreach ($facility->readingAreas as $area)
                                            <a
                                                href="{{ route('reading-areas.show', $area) }}"
                                                class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700 hover:bg-blue-100 hover:text-blue-900 border border-slate-200 transition"
                                                title="Lantai {{ $area->floor_number }}"
                                            >
                                                <x-icon name="map" class="h-3 w-3 text-slate-400" />
                                                {{ $area->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs italic text-slate-400">Belum dipetakan ke area mana pun</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($facility->status === 'available')
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
                                        href="{{ route('facilities.show', $facility) }}"
                                        class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-blue-900"
                                        title="Lihat Detail Fasilitas"
                                    >
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                    <a
                                        href="{{ route('facilities.edit', $facility) }}"
                                        class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-amber-700"
                                        title="Edit Fasilitas"
                                    >
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form
                                        action="{{ route('facilities.destroy', $facility) }}"
                                        method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus fasilitas \'{{ $facility->name }}\'?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-lg p-1.5 text-slate-500 hover:bg-rose-50 hover:text-rose-700"
                                            title="Hapus Fasilitas"
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
                                    <x-icon name="building-office" class="h-6 w-6" />
                                </div>
                                <h3 class="mt-3 text-sm font-semibold text-slate-800">Tidak ada fasilitas</h3>
                                <p class="mt-1 text-sm text-slate-500">Belum ada fasilitas yang cocok dengan filter pencarian.</p>
                                <div class="mt-4">
                                    <a
                                        href="{{ route('facilities.create') }}"
                                        class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800"
                                    >
                                        <x-icon name="plus" class="h-4 w-4" />
                                        <span>Tambah Fasilitas</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($facilities->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $facilities->links() }}
            </div>
        @endif
    </div>
@endsection

