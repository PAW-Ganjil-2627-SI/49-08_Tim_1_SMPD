@extends('layouts.app')

@section('title', 'Edit Buku')

@section('content')
    <x-page-header
        title="Edit Buku"
        :description="$book->title"
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Katalog & Buku' => route('books.index'), 'Edit Buku' => null]"
    />

    @include('books.partials.form', [
        'action' => route('books.update', $book),
        'method' => 'PUT',
        'submitLabel' => 'Simpan Perubahan',
    ])
@endsection
