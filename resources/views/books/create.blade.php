@extends('layouts.app')

@section('title', 'Tambah Buku')

@section('content')
    <x-page-header
        title="Tambah Buku"
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Katalog & Buku' => route('books.index'), 'Tambah Buku' => null]"
    />

    @include('books.partials.form', [
        'action' => route('books.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan',
    ])
@endsection
