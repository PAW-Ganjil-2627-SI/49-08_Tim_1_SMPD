@extends('layouts.app')

@section('title', 'Detail Fasilitas: ' . $facility->name)

@section('content')
    <x-page-header
        :title="$facility->name"
        description="Detail spesifikasi fasilitas dan daftar area baca yang menyediakannya."
        :breadcrumbs="[
            'Dashboard' => route('dashboard'),
            'Fasilitas & Area' => route('facilities.index'),
            $facility->name => null,
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('facilities.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                >
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    <span>Kembali</span>
                </a>
                <a
                    href="{{ route('facilities.edit', $facility) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500"
                >
                    <x-icon name="pencil" class="h-4 w-4" />
                    <span>Edit Fasilitas</span>
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
                        class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500"
                    >
                        <x-icon name="trash" class="h-4 w-4" />
                        <span>Hapus</span>
                    </button>
                </form>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Kolom Kiri: Detail Spesifikasi Fasilitas -->
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status Fasilitas</span>
                    @if ($facility->status === 'available')
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 border border-emerald-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Tersedia
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 border border-rose-200">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            Tidak Tersedia
                        </span>
                    @endif
                </div>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Jenis / Tipe Kategori</dt>
                        <dd class="mt-1 font-semibold text-slate-900 capitalize flex items-center gap-2">
                            <x-icon name="archive" class="h-4 w-4 text-slate-400" />
                            <span>{{ $facility->type }}</span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-slate-500">Jumlah Area yang Memiliki</dt>
                        <dd class="mt-1 font-semibold text-slate-900 flex items-center gap-2">
                            <x-icon name="map" class="h-4 w-4 text-slate-400" />
                            <span>{{ $facility->readingAreas->count() }} Area Baca</span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-slate-500">Deskripsi / Catatan Teknis</dt>
                        <dd class="mt-1 text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-100">
                            {{ $facility->description ?: 'Tidak ada deskripsi tambahan.' }}
                        </dd>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-xs text-slate-400">
                        <div>Didaftarkan: {{ $facility->created_at?->format('d M Y, H:i') ?? '-' }}</div>
                        <div>Terakhir diubah: {{ $facility->updated_at?->format('d M Y, H:i') ?? '-' }}</div>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Kolom Kanan: Area Baca yang Menyediakan Fasilitas ini -->
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                            <x-icon name="map" class="h-5 w-5 text-blue-900" />
                            <span>Tersedia di Area Baca Berikut</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar ruangan atau bilik belajar yang menyediakan fasilitas ini.</p>
                    </div>

                    <a
                        href="{{ route('facilities.edit', $facility) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-blue-900 hover:bg-slate-100 transition"
                    >
                        <x-icon name="pencil" class="h-3.5 w-3.5" />
                        <span>Kelola Pemetaan Area</span>
                    </a>
                </div>

                @if ($facility->readingAreas->isNotEmpty())
                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($facility->readingAreas as $area)
                            <div class="rounded-xl border border-slate-200 p-4 hover:border-blue-200 hover:shadow-sm transition bg-white flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <a href="{{ route('reading-areas.show', $area) }}" class="font-semibold text-slate-900 hover:text-blue-900 hover:underline">
                                            {{ $area->name }}
                                        </a>
                                        <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-800 border border-blue-100">
                                            Lantai {{ $area->floor_number }}
                                        </span>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500">
                                        {{ $area->description ?: 'Tidak ada catatan lokasi.' }}
                                    </p>
                                </div>

                                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Kapasitas: <strong>{{ $area->capacity }} orang</strong></span>
                                    @if ($area->status === 'available')
                                        <span class="font-medium text-emerald-700 flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Area Tersedia
                                        </span>
                                    @else
                                        <span class="font-medium text-rose-700 flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                            Tidak Tersedia
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <x-icon name="map" class="h-6 w-6" />
                        </div>
                        <h4 class="mt-3 text-sm font-semibold text-slate-800">Belum dipetakan ke area baca</h4>
                        <p class="mt-1 text-xs text-slate-500">Fasilitas ini belum dihubungkan ke ruangan atau bilik baca mana pun.</p>
                        <div class="mt-4">
                            <a
                                href="{{ route('facilities.edit', $facility) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-900 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-800"
                            >
                                <x-icon name="plus" class="h-3.5 w-3.5" />
                                <span>Petakan ke Area Sekarang</span>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

