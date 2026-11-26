<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Publisher;
use App\Models\ReadingArea;
use App\Models\User;
use App\Support\Navigation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{

    public function index(Request $request): View
    {
        $user = $request->user();

        $publishersByType = Publisher::query()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $stats = [
            'books' => Book::count(),
            'book_copies' => (int) Book::sum('stock'),
            'members' => Member::count(),
            'active_members' => Member::where('status', 'active')->count(),
            'publishers' => (int) $publishersByType->sum(),
            'publisher_count' => (int) $publishersByType->get('publisher', 0),
            'distributor_count' => (int) $publishersByType->get('distributor', 0),
            'reading_areas' => ReadingArea::count(),
            'available_reading_areas' => ReadingArea::where('status', 'available')->count(),
        ];

        $latestBooks = Book::query()
            ->with(['category:id,name', 'publisher:id,name'])
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'category_id', 'publisher_id', 'publication_year', 'created_at']);

        $usersByRole = $user->role === 'admin'
            ? User::query()->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role')
            : null;

        return view('dashboard.index', [
            'stats' => $stats,
            'latestBooks' => $latestBooks,
            'usersByRole' => $usersByRole,
            'modules' => Navigation::modules($user),
        ]);
    }
}
