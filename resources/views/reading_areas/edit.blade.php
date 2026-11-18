@extends('layouts.app')

@section('title', 'Edit Area Baca: ' . $readingArea->name)

@section('content')
    <x-page-header
        title="Edit Area Baca"
        :description="'Perbarui data \'' . $readingArea->name . '\' dan sesuaikan fasilitas yang terhubung.'"
        :breadcrumbs="[
            'Dashboard' => route('dashboard'),
            'Fasilitas & Area' => route('reading-areas.index'),
            $readingArea->name => route('reading-areas.show', $readingArea),
            'Edit' => null,
        ]"
    >
        <x-slot:actions>
            <a
                href="{{ route('reading-areas.show', $readingArea) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
            >
                <x-icon name="arrow-left" class="h-4 w-4" />
                <span>Lihat Detail</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-4xl">
        <form method="POST" action="{{ route('reading-areas.update', $readingArea) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Card Informasi Pokok Area -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <x-icon name="map" class="h-5 w-5 text-blue-900" />
                    <span>Informasi Area Baca</span>
                </h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Nama Area -->
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-slate-700">
                            Nama Area / Ruangan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $readingArea->name) }}"
                            required
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 @error('name') border-rose-300 @enderror"
                        />
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nomor Lantai -->
                    <div>
                        <label for="floor_number" class="block text-sm font-medium text-slate-700">
                            Nomor Lantai <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="number"
                            name="floor_number"
                            id="floor_number"
                            value="{{ old('floor_number', $readingArea->floor_number) }}"
                            min="0"
                            max="50"
                            required
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 @error('floor_number') border-rose-300 @enderror"
                        />
                        @error('floor_number')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kapasitas Orang -->
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-slate-700">
                            Kapasitas (Orang) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="number"
                            name="capacity"
                            id="capacity"
                            value="{{ old('capacity', $readingArea->capacity) }}"
                            min="1"
                            max="1000"
                            required
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 @error('capacity') border-rose-300 @enderror"
                        />
                        @error('capacity')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Ketersediaan -->
                    <div class="sm:col-span-2">
                        <label for="status" class="block text-sm font-medium text-slate-700">
                            Status Ketersediaan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="status"
                            id="status"
                            required
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 @error('status') border-rose-300 @enderror"
                        >
                            <option value="available" @selected(old('status', $readingArea->status) === 'available')>Tersedia (Dapat digunakan langsung)</option>
                            <option value="unavailable" @selected(old('status', $readingArea->status) === 'unavailable')>Tidak Tersedia (Sedang perbaikan/ditutup)</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi Catatan -->
                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-slate-700">
                            Deskripsi / Catatan Lokasi (Opsional)
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 @error('description') border-rose-300 @enderror"
                        >{{ old('description', $readingArea->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Card Relasi Fasilitas -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                            <x-icon name="building-office" class="h-5 w-5 text-blue-900" />
                            <span>Hubungkan Fasilitas yang Ada di Area Ini</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih atau perbarui fasilitas yang terhubung dengan area ini.</p>
                    </div>
                    <a
                        href="{{ route('facilities.create') }}"
                        target="_blank"
                        class="text-xs font-semibold text-blue-700 hover:text-blue-900 hover:underline flex items-center gap-1"
                    >
                        <x-icon name="plus" class="h-3.5 w-3.5" />
                        <span>Fasilitas Baru</span>
                    </a>
                </div>

                @if ($facilities->isNotEmpty())
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($facilities as $facility)
                            <label class="relative flex items-start p-3 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition">
                                <div class="flex h-5 items-center">
                                    <input
                                        type="checkbox"
                                        name="facilities[]"
                                        value="{{ $facility->id }}"
                                        @checked(in_array($facility->id, old('facilities', $selectedFacilities)))
                                        class="h-4 w-4 rounded border-slate-300 text-blue-900 focus:ring-blue-600"
                                    />
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-slate-800">{{ $facility->name }}</span>
                                    <span class="block text-xs text-slate-500 capitalize">Tipe: {{ $facility->type }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-slate-300 p-4 text-center">
                        <p class="text-xs text-slate-500">Belum ada fasilitas yang terdaftar di sistem.</p>
                    </div>
                @endif

                @error('facilities')
                    <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Aksi Form -->
            <div class="flex items-center justify-end gap-3">
                <a
                    href="{{ route('reading-areas.show', $readingArea) }}"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
                >
                    <x-icon name="check-circle" class="h-4 w-4" />
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
@endsection

