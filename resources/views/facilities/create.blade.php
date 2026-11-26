@extends('layouts.app')

@section('title', 'Tambah Fasilitas Baru')

@section('content')
    <x-page-header
        title="Tambah Fasilitas Baru"
        description="Daftarkan fasilitas, sarana, atau prasarana baru dan petakan ke area baca yang memilikinya."
        :breadcrumbs="[
            'Dashboard' => route('dashboard'),
            'Fasilitas & Area' => route('facilities.index'),
            'Tambah Fasilitas' => null,
        ]"
    >
        <x-slot:actions>
            <a
                href="{{ route('facilities.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
            >
                <x-icon name="arrow-left" class="h-4 w-4" />
                <span>Kembali ke Daftar</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-4xl">
        <form method="POST" action="{{ route('facilities.store') }}" class="space-y-6">
            @csrf

            <!-- Card Informasi Pokok Fasilitas -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <x-icon name="building-office" class="h-5 w-5 text-blue-900" />
                    <span>Informasi Fasilitas</span>
                </h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Nama Fasilitas -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">
                            Nama Fasilitas <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            placeholder="Contoh: Wi-Fi High-Speed, AC Central, Stopkontak"
                            required
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 @error('name') border-rose-300 @enderror"
                        />
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jenis / Kategori Tipe -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700">
                            Jenis / Tipe Fasilitas <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="type"
                            id="type"
                            list="type-suggestions"
                            value="{{ old('type') }}"
                            placeholder="Pilih atau ketik tipe (misal: kelistrikan, jaringan)"
                            required
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 @error('type') border-rose-300 @enderror"
                        />
                        <datalist id="type-suggestions">
                            @foreach ($commonTypes as $type)
                                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                            @endforeach
                        </datalist>
                        @error('type')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-slate-400">Contoh: jaringan, kelistrikan, kenyamanan, elektronik, furnitur.</p>
                    </div>

                    <!-- Status Fasilitas -->
                    <div class="sm:col-span-2">
                        <label for="status" class="block text-sm font-medium text-slate-700">
                            Status Fasilitas <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="status"
                            id="status"
                            required
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 @error('status') border-rose-300 @enderror"
                        >
                            <option value="available" @selected(old('status', 'available') === 'available')>Tersedia (Berfungsi normal dan siap pakai)</option>
                            <option value="unavailable" @selected(old('status') === 'unavailable')>Tidak Tersedia (Rusak / Sedang dalam perbaikan)</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi Catatan -->
                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-slate-700">
                            Deskripsi / Spesifikasi Fasilitas (Opsional)
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            placeholder="Keterangan spesifikasi teknis, aturan penggunaan, atau petunjuk operasional..."
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 @error('description') border-rose-300 @enderror"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Card Hubungkan ke Area Baca -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                            <x-icon name="map" class="h-5 w-5 text-blue-900" />
                            <span>Tersedia di Area Mana Saja?</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Centang area baca yang menyediakan fasilitas ini (dapat diubah kapan saja).</p>
                    </div>
                </div>

                @if ($readingAreas->isNotEmpty())
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($readingAreas as $area)
                            <label class="relative flex items-start p-3 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition">
                                <div class="flex h-5 items-center">
                                    <input
                                        type="checkbox"
                                        name="reading_areas[]"
                                        value="{{ $area->id }}"
                                        @checked(in_array($area->id, old('reading_areas', [])))
                                        class="h-4 w-4 rounded border-slate-300 text-blue-900 focus:ring-blue-600"
                                    />
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-slate-800">{{ $area->name }}</span>
                                    <span class="block text-xs text-slate-500">Lantai {{ $area->floor_number }} · Kapasitas {{ $area->capacity }} org</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-slate-300 p-4 text-center">
                        <p class="text-xs text-slate-500">Belum ada area baca yang terdaftar.</p>
                    </div>
                @endif

                @error('reading_areas')
                    <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Aksi Form -->
            <div class="flex items-center justify-end gap-3">
                <a
                    href="{{ route('facilities.index') }}"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
                >
                    <x-icon name="check-circle" class="h-4 w-4" />
                    <span>Simpan Fasilitas</span>
                </button>
            </div>
        </form>
    </div>
@endsection

