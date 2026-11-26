@extends('layouts.app')

@section('title', 'Ubah Penerbit & Supplier - ' . $publisher->name)

@section('content')
    <x-page-header
        :title="'Ubah Data: ' . $publisher->name"
        description="Perbarui informasi penerbit atau distributor, kontak sales, alamat, dan wilayah operasional."
        :breadcrumbs="[
            'Dashboard' => route('dashboard'),
            'Penerbit & Supplier' => route('publishers.index'),
            $publisher->name => route('publishers.show', $publisher),
            'Ubah' => null,
        ]"
    />

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ route('publishers.update', $publisher) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            @method('PUT')

            @include('publishers._form')

            <div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                <a
                    href="{{ route('publishers.show', $publisher) }}"
                    class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 transition"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-md bg-blue-900 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 transition"
                >
                    <x-icon name="check-circle" class="h-4 w-4 shrink-0" />
                    Perbarui Data
                </button>
            </div>
        </form>
    </div>
@endsection

