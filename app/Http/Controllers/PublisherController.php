<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublisherRequest;
use App\Models\Publisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublisherController extends Controller
{
    /**
     * Tampilkan daftar penerbit dan supplier/distributor.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $type = $request->string('type')->trim()->value();

        $publishers = Publisher::query()
            ->withCount('books')
            ->search($search)
            ->ofType($type)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $totalCount = Publisher::count();
        $publisherCount = Publisher::where('type', 'publisher')->count();
        $distributorCount = Publisher::where('type', 'distributor')->count();

        return view('publishers.index', [
            'publishers' => $publishers,
            'search' => $search,
            'selectedType' => $type,
            'totalCount' => $totalCount,
            'publisherCount' => $publisherCount,
            'distributorCount' => $distributorCount,
        ]);
    }

    /**
     * Tampilkan formulir untuk menambahkan penerbit/distributor baru.
     */
    public function create(): View
    {
        return view('publishers.create');
    }

    /**
     * Simpan data penerbit/distributor baru ke database.
     */
    public function store(PublisherRequest $request): RedirectResponse
    {
        Publisher::create($request->validated());

        return redirect()
            ->route('publishers.index')
            ->with('success', 'Data penerbit/supplier berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail penerbit/distributor.
     */
    public function show(Publisher $publisher): View
    {
        $publisher->load(['books' => fn ($query) => $query->latest()->limit(10)]);
        $publisher->loadCount('books');

        return view('publishers.show', [
            'publisher' => $publisher,
        ]);
    }

    /**
     * Tampilkan formulir edit data penerbit/distributor.
     */
    public function edit(Publisher $publisher): View
    {
        return view('publishers.edit', [
            'publisher' => $publisher,
        ]);
    }

    /**
     * Perbarui data penerbit/distributor di database.
     */
    public function update(PublisherRequest $request, Publisher $publisher): RedirectResponse
    {
        $publisher->update($request->validated());

        return redirect()
            ->route('publishers.index')
            ->with('success', 'Data penerbit/supplier berhasil diperbarui.');
    }

    /**
     * Hapus data penerbit/distributor.
     */
    public function destroy(Publisher $publisher): RedirectResponse
    {
        if ($publisher->books()->exists()) {
            return redirect()
                ->route('publishers.index')
                ->with('error', 'Penerbit/supplier tidak dapat dihapus karena masih terhubung dengan koleksi buku.');
        }

        $publisher->delete();

        return redirect()
            ->route('publishers.index')
            ->with('success', 'Data penerbit/supplier berhasil dihapus.');
    }
}
