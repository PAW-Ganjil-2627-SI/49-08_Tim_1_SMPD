{{--
    Notifikasi sesi global (dipasang sekali di layouts/app.blade.php). Dari controller:
        return redirect()->route('books.index')->with('success', 'Buku berhasil disimpan.');
        return back()->with('error', 'Buku gagal dihapus.');
        return back()->with('warning', 'Stok buku hampir habis.');
        return back()->with('info', 'Tidak ada perubahan data.');

    Hanya "success" yang hilang sendiri (6 detik). Error, warning, dan info tetap tampil
    sampai ditutup. Pesan kesalahan validasi per kolom tetap tampil di bawah inputnya.
--}}
@php
    $types = [
        'success' => ['icon' => 'check-circle', 'class' => 'border-green-200 bg-green-50 text-green-800'],
        'error' => ['icon' => 'exclamation-circle', 'class' => 'border-red-200 bg-red-50 text-red-800'],
        'warning' => ['icon' => 'exclamation-triangle', 'class' => 'border-amber-200 bg-amber-50 text-amber-800'],
        'info' => ['icon' => 'information-circle', 'class' => 'border-blue-200 bg-blue-50 text-blue-800'],
    ];
@endphp

@foreach ($types as $type => $style)
    @if (session($type))
        <div
            x-data="{ show: true }"
            x-show="show"
            @if ($type === 'success') x-init="setTimeout(() => show = false, 6000)" @endif
            data-flash="{{ $type }}"
            role="{{ in_array($type, ['error', 'warning']) ? 'alert' : 'status' }}"
            class="mb-6 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm {{ $style['class'] }}"
        >
            <x-icon :name="$style['icon']" class="mt-0.5 h-5 w-5 shrink-0" />
            <p class="min-w-0 flex-1 break-words">{{ session($type) }}</p>
            <button type="button" class="rounded p-0.5 opacity-70 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-current" @click="show = false">
                <span class="sr-only">Tutup notifikasi</span>
                <x-icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>
    @endif
@endforeach
