<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\ReadingArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FacilityController extends Controller
{
    /**
     * Tampilkan daftar fasilitas beserta area yang menggunakannya (Read).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $type = $request->query('type');
        $status = $request->query('status');

        $query = Facility::query()->with('readingAreas')->withCount('readingAreas');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $facilities = $query->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total_facilities' => Facility::count(),
            'available_facilities' => Facility::where('status', 'available')->count(),
            'total_areas' => ReadingArea::count(),
            'distinct_types' => Facility::distinct('type')->count('type'),
        ];

        // Daftar jenis/kategori fasilitas untuk dropdown filter
        $types = Facility::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('facilities.index', [
            'facilities' => $facilities,
            'stats' => $stats,
            'types' => $types,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Form tambah fasilitas baru (Create).
     */
    public function create(): View
    {
        $readingAreas = ReadingArea::orderBy('floor_number')->orderBy('name')->get();

        // Kategori tipe yang umum digunakan
        $commonTypes = ['kelistrikan', 'jaringan', 'kenyamanan', 'elektronik', 'furnitur', 'penyimpanan'];

        return view('facilities.create', [
            'readingAreas' => $readingAreas,
            'commonTypes' => $commonTypes,
        ]);
    }

    /**
     * Simpan data fasilitas baru dan hubungkan ke area baca jika dipilih (Store).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['available', 'unavailable'])],
            'reading_areas' => ['nullable', 'array'],
            'reading_areas.*' => ['exists:reading_areas,id'],
        ]);

        $facility = Facility::create([
            'name' => $validated['name'],
            'type' => strtolower(trim($validated['type'])),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        // Hubungkan ke area baca (many-to-many pivot)
        $facility->readingAreas()->sync($validated['reading_areas'] ?? []);

        return redirect()
            ->route('facilities.index')
            ->with('success', 'Fasilitas "'.$facility->name.'" berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail fasilitas dan daftar area yang menggunakannya (Read/Detail).
     */
    public function show(Facility $facility): View
    {
        $facility->load('readingAreas');

        return view('facilities.show', [
            'facility' => $facility,
        ]);
    }

    /**
     * Form edit fasilitas (Edit).
     */
    public function edit(Facility $facility): View
    {
        $facility->load('readingAreas');
        $readingAreas = ReadingArea::orderBy('floor_number')->orderBy('name')->get();
        $selectedAreas = $facility->readingAreas->pluck('id')->toArray();
        $commonTypes = ['kelistrikan', 'jaringan', 'kenyamanan', 'elektronik', 'furnitur', 'penyimpanan'];

        return view('facilities.edit', [
            'facility' => $facility,
            'readingAreas' => $readingAreas,
            'selectedAreas' => $selectedAreas,
            'commonTypes' => $commonTypes,
        ]);
    }

    /**
     * Perbarui data fasilitas dan sinkronkan relasi ke area baca (Update).
     */
    public function update(Request $request, Facility $facility): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['available', 'unavailable'])],
            'reading_areas' => ['nullable', 'array'],
            'reading_areas.*' => ['exists:reading_areas,id'],
        ]);

        $facility->update([
            'name' => $validated['name'],
            'type' => strtolower(trim($validated['type'])),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        // Sinkronisasi relasi area baca
        $facility->readingAreas()->sync($validated['reading_areas'] ?? []);

        return redirect()
            ->route('facilities.index')
            ->with('success', 'Fasilitas "'.$facility->name.'" berhasil diperbarui.');
    }

    /**
     * Hapus fasilitas dari database (Delete).
     */
    public function destroy(Facility $facility): RedirectResponse
    {
        $name = $facility->name;

        // Tabel pivot area_facilities terhapus otomatis melalui cascade on delete di migration
        $facility->delete();

        return redirect()
            ->route('facilities.index')
            ->with('success', 'Fasilitas "'.$name.'" berhasil dihapus.');
    }
}

