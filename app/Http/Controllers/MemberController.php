<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\State;
use App\Models\Ward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Member::with(['state', 'lga', 'ward', 'pollingUnit'])
            ->withCount([
                'referrals',
                'referrals as verified_referrals_count' => function ($query) {
                    $query->where('status', 'verified');
                },
            ])
            ->accessibleBy($request->user());

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('membership_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($stateId = $request->input('state_id')) {
            $query->where('state_id', $stateId);
        }

        if ($lgaId = $request->input('lga_id')) {
            $query->where('lga_id', $lgaId);
        }

        if ($wardId = $request->input('ward_id')) {
            $query->where('ward_id', $wardId);
        }

        if ($pollingUnitId = $request->input('polling_unit_id')) {
            $query->where('polling_unit_id', $pollingUnitId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($request->input('get_all_ids') === '1') {
            return response()->json($query->pluck('id'));
        }

        $members = $query->latest('registered_at')->paginate(15)->withQueryString();

        return Inertia::render('Members/Index', [
            'members' => $members,
            'filters' => $request->only(['search', 'state_id', 'lga_id', 'ward_id', 'polling_unit_id', 'status']),
            'states' => State::with('lgas.wards')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Members/Create', [
            'states' => State::with('lgas.wards')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Male,Female'],
            'dob' => ['required', 'date', 'before:today'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'state_id' => ['required', 'exists:states,id'],
            'lga_id' => ['required', 'exists:lgas,id'],
            'ward_id' => ['required', 'exists:wards,id'],
            'polling_unit_id' => ['nullable', 'exists:polling_units,id'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        // Generate auto membership number
        $latestId = Member::max('id') ?? 0;
        $nextNumber = str_pad((string) ($latestId + 1), 8, '0', STR_PAD_LEFT);
        $membershipNumber = 'SAJ-'.date('Y').'-'.$nextNumber;

        $member = Member::create([
            'membership_number' => $membershipNumber,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'dob' => $validated['dob'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'photo_path' => $photoPath,
            'state_id' => $validated['state_id'],
            'lga_id' => $validated['lga_id'],
            'ward_id' => $validated['ward_id'],
            'polling_unit_id' => $validated['polling_unit_id'] ?? null,
            'status' => 'pending',
            'registered_at' => now(),
        ]);

        return redirect()->route('members.show', $member)->with('success', 'Member registered successfully.');
    }

    public function show(Request $request, Member $member): Response
    {
        // Authorize jurisdiction
        if (! Member::accessibleBy($request->user())->where('id', $member->id)->exists()) {
            abort(403, 'Unauthorized jurisdiction access.');
        }

        $member->load(['state', 'lga', 'ward', 'pollingUnit', 'verifications.verifiedBy']);

        return Inertia::render('Members/Show', [
            'member' => $member,
        ]);
    }

    public function pollingUnits(Ward $ward): JsonResponse
    {
        return response()->json($ward->pollingUnits()->orderBy('code')->get());
    }
}
