{{--
    Navbar halaman publik sederhana: identitas brand & tombol masuk.
--}}
<header class="border-b border-slate-200 bg-white">
    <nav class="mx-auto flex h-16 max-w-5xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
        <a href="{{ url('/') }}" class="min-w-0 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
            <x-brand subtitle="Sistem Manajemen Perpustakaan Digital" />
        </a>

        <div>
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                    Buka Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                    Masuk
                </a>
            @endauth
        </div>
    </nav>
</header>
