@props([
    'active' => 'areas', // 'areas' | 'facilities'
])

@php
    $areaCount = \App\Models\ReadingArea::count();
    $facilityCount = \App\Models\Facility::count();
@endphp

<div class="mb-6 border-b border-slate-200">
    <nav class="-mb-px flex space-x-6" aria-label="Tabs Modul Fasilitas & Area">
        <a
            href="{{ route('reading-areas.index') }}"
            @class([
                'inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium transition',
                'border-blue-900 text-blue-900 font-semibold' => $active === 'areas',
                'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' => $active !== 'areas',
            ])
        >
            <x-icon name="map" class="h-4 w-4" />
            <span>Area Baca</span>
            <span @class([
                'rounded-full px-2 py-0.5 text-xs font-semibold',
                'bg-blue-100 text-blue-900' => $active === 'areas',
                'bg-slate-100 text-slate-600' => $active !== 'areas',
            ])>
                {{ $areaCount }}
            </span>
        </a>

        <a
            href="{{ route('facilities.index') }}"
            @class([
                'inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium transition',
                'border-blue-900 text-blue-900 font-semibold' => $active === 'facilities',
                'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' => $active !== 'facilities',
            ])
        >
            <x-icon name="building-office" class="h-4 w-4" />
            <span>Fasilitas</span>
            <span @class([
                'rounded-full px-2 py-0.5 text-xs font-semibold',
                'bg-blue-100 text-blue-900' => $active === 'facilities',
                'bg-slate-100 text-slate-600' => $active !== 'facilities',
            ])>
                {{ $facilityCount }}
            </span>
        </a>
    </nav>
</div>

