<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $typeId = $request->query('membership_type_id');
        $status = $request->query('status');

        $query = Member::query()->with('membershipType');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('membership_number', 'like', "%{$search}%")
                    ->orWhere('card_number', 'like', "%{$search}%")
                    ->orWhere('identity_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($typeId) {
            $query->where('membership_type_id', $typeId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $members = $query->latest('id')->paginate(10)->withQueryString();

        $stats = [
            'total_members'   => Member::count(),
            'active_members'  => Member::where('status', 'active')->count(),
            'inactive_members'=> Member::where('status', 'inactive')->count(),
            'total_types'     => MembershipType::count(),
        ];

        $membershipTypes = MembershipType::orderBy('name')->get();

        return view('members.index', [
            'members'         => $members,
            'stats'           => $stats,
            'membershipTypes' => $membershipTypes,
            'filters'         => [
                'search'             => $search,
                'membership_type_id' => $typeId,
                'status'             => $status,
            ],
        ]);
    }

    public function create(): View
    {
        $this->ensureDefaultMembershipTypesExist();

        $membershipTypes = MembershipType::orderBy('name')->get();

        $year        = date('Y');
        $nextNumber  = Member::max('id') + 1;
        $suggestedNumber     = sprintf('AGT-%s-%04d', $year, $nextNumber);
        $suggestedCardNumber = sprintf('KARTU-%s-%04d', $year, $nextNumber);

        return view('members.create', [
            'membershipTypes'     => $membershipTypes,
            'suggestedNumber'     => $suggestedNumber,
            'suggestedCardNumber' => $suggestedCardNumber,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'membership_number'  => ['required', 'string', 'max:50', 'unique:members,membership_number'],
            'card_number'        => ['nullable', 'string', 'max:50', 'unique:members,card_number'],
            'identity_number'    => ['nullable', 'string', 'max:50'],
            'membership_type_id' => ['required', 'exists:membership_types,id'],
            'email'              => ['nullable', 'email', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:50'],
            'address'            => ['nullable', 'string'],
            'join_date'          => ['required', 'date'],
            'status'             => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if (blank($validated['card_number'] ?? null)) {
            $validated['card_number'] = $validated['membership_number'];
        }

        $member = Member::create($validated);

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Anggota "' . $member->name . '" berhasil didaftarkan. ID Card siap dicetak.');
    }

    public function show(Member $member): View
    {
        $member->load('membershipType');

        return view('members.show', ['member' => $member]);
    }

    public function edit(Member $member): View
    {
        $membershipTypes = MembershipType::orderBy('name')->get();

        return view('members.edit', [
            'member'          => $member,
            'membershipTypes' => $membershipTypes,
        ]);
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'membership_number'  => ['required', 'string', 'max:50', Rule::unique('members', 'membership_number')->ignore($member->id)],
            'card_number'        => ['nullable', 'string', 'max:50', Rule::unique('members', 'card_number')->ignore($member->id)],
            'identity_number'    => ['nullable', 'string', 'max:50'],
            'membership_type_id' => ['required', 'exists:membership_types,id'],
            'email'              => ['nullable', 'email', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:50'],
            'address'            => ['nullable', 'string'],
            'join_date'          => ['required', 'date'],
            'status'             => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if (blank($validated['card_number'] ?? null)) {
            $validated['card_number'] = $validated['membership_number'];
        }

        $member->update($validated);

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Data anggota "' . $member->name . '" berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $name = $member->name;
        $member->delete();

        return redirect()
            ->route('members.index')
            ->with('success', 'Anggota "' . $name . '" berhasil dihapus.');
    }

    public function printCard(Member $member): View
    {
        $member->load('membershipType');

        return view('members.print-card', ['member' => $member]);
    }

    protected function ensureDefaultMembershipTypesExist(): void
    {
        if (MembershipType::count() === 0) {
            $defaults = [
                [
                    'name'               => 'Reguler',
                    'description'        => 'Keanggotaan umum standar perpustakaan',
                    'duration_months'    => 12,
                    'max_borrowed_books' => 3,
                ],
                [
                    'name'               => 'Pelajar / Mahasiswa',
                    'description'        => 'Pelajar dan mahasiswa dengan kartu pelajar/KTM',
                    'duration_months'    => 12,
                    'max_borrowed_books' => 5,
                ],
                [
                    'name'               => 'Guru / Dosen',
                    'description'        => 'Tenaga pengajar dan civitas akademika',
                    'duration_months'    => 24,
                    'max_borrowed_books' => 10,
                ],
            ];

            foreach ($defaults as $type) {
                MembershipType::firstOrCreate(['name' => $type['name']], $type);
            }
        }
    }
}
