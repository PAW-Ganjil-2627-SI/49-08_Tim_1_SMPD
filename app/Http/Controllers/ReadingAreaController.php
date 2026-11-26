<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\ReadingArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReadingAreaController extends Controller
{
    
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $floor = $request->query('floor_number');
        $status = $request->query('status');
        $facilityId = $request->query('facility_id');

        $query = ReadingArea::query()->with('facilities');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($floor !== null && $floor !== '') {
            $query->where('floor_number', $floor);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($facilityId) {
            $query->whereHas('facilities', function ($q) use ($facilityId) {
                $q->where('facilities.id', $facilityId);
            });
        }

        $readingAreas = $query->orderBy('floor_number')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total_areas' => ReadingArea::count(),
            'available_areas' => ReadingArea::where('status', 'available')->count(),
            'total_capacity' => (int) ReadingArea::sum('capacity'),
            'total_facilities' => Facility::count(),
        ];

        $availableFacilities = Facility::orderBy('name')->get(['id', 'name', 'type']);

        $existingFloors = ReadingArea::select('floor_number')
            ->distinct()
            ->orderBy('floor_number')
            ->pluck('floor_number');

        return view('reading_areas.index', [
            'readingAreas' => $readingAreas,
            'stats' => $stats,
            'availableFacilities' => $availableFacilities,
            'existingFloors' => $existingFloors,
            'filters' => [
                'search' => $search,
                'floor_number' => $floor,
                'status' => $status,
                'facility_id' => $facilityId,
            ],
        ]);
    }

    public function create(): View
    {
        $facilities = Facility::orderBy('type')->orderBy('name')->get();

        return view('reading_areas.create', [
            'facilities' => $facilities,
        ]);
    }

    
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'floor_number' => ['required', 'integer', 'min:0', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['available', 'unavailable'])],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['exists:facilities,id'],
        ]);

        $readingArea = ReadingArea::create([
            'name' => $validated['name'],
            'floor_number' => $validated['floor_number'],
            'capacity' => $validated['capacity'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        $readingArea->facilities()->sync($validated['facilities'] ?? []);

        return redirect()
            ->route('reading-areas.index')
            ->with('success', 'Area baca "'.$readingArea->name.'" berhasil ditambahkan.');
    }

    
    public function show(ReadingArea $readingArea): View
    {
        $readingArea->load('facilities');

        return view('reading_areas.show', [
            'readingArea' => $readingArea,
        ]);
    }

    
    public function edit(ReadingArea $readingArea): View
    {
        $readingArea->load('facilities');
        $facilities = Facility::orderBy('type')->orderBy('name')->get();
        $selectedFacilities = $readingArea->facilities->pluck('id')->toArray();

        return view('reading_areas.edit', [
            'readingArea' => $readingArea,
            'facilities' => $facilities,
            'selectedFacilities' => $selectedFacilities,
        ]);
    }

    
    public function update(Request $request, ReadingArea $readingArea): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'floor_number' => ['required', 'integer', 'min:0', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['available', 'unavailable'])],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['exists:facilities,id'],
        ]);

        $readingArea->update([
            'name' => $validated['name'],
            'floor_number' => $validated['floor_number'],
            'capacity' => $validated['capacity'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        $readingArea->facilities()->sync($validated['facilities'] ?? []);

        return redirect()
            ->route('reading-areas.index')
            ->with('success', 'Area baca "'.$readingArea->name.'" berhasil diperbarui.');
    }

    
    public function destroy(ReadingArea $readingArea): RedirectResponse
    {
        $name = $readingArea->name;

        $readingArea->delete();

        return redirect()
            ->route('reading-areas.index')
            ->with('success', 'Area baca "'.$name.'" berhasil dihapus.');
    }
}

