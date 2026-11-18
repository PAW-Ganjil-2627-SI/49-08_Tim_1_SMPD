@extends('layouts.public')

@section('title', 'Sistem Manajemen Perpustakaan Digital')

@php
    $modules = collect(config('navigation.sidebar', []))
        ->flatMap(fn (array $group) => $group['items'] ?? [])
        ->where('module', true)
        ->values();
@endphp

@section('content')
    <section class="w-full py-12">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="mt-6 text-3xl font-bold tracking-tight text-slate-900 sm:text-5xl">
                Kelola Perpustakaan dengan Lebih Mudah
            </h1>

            <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-slate-600 sm:text-lg">
                SMPD dirancang untuk membantu petugas mengelola katalog buku, data anggota,
                penerbit, serta fasilitas perpustakaan melalui satu sistem yang terintegrasi.
            </p>

            <div class="mt-8 flex justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-900 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                        Buka Dashboard
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-900 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                        Masuk ke Sistem
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                @endauth
            </div>
        </div>
    </section>
@endsection
