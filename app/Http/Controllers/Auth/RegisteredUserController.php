<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\EscoOfficial;
use App\Models\Member;
use App\Models\State;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the public member self-registration page.
     */
    public function create(): Response
    {
        $announcements = Announcement::with(['state'])
            ->where('target_level', 'national')
            ->latest()
            ->limit(3)
            ->get();

        $escos = EscoOfficial::with(['position', 'state', 'lga', 'ward'])
            ->where('status', 'active')
            ->latest()
            ->limit(3)
            ->get();

        return Inertia::render('auth/Register', [
            'states' => State::with('lgas.wards')->get(),
            'announcements' => $announcements,
            'escos' => $escos,
        ]);
    }

    /**
     * Handle an incoming member self-registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Male,Female'],
            'dob' => ['required', 'date', 'before:today'],
            'phone' => ['required', 'string', 'regex:/^\+234[789][01]\d{8}$/', 'unique:members,phone'],
            'email' => ['nullable', 'email', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'state_id' => ['required', 'exists:states,id'],
            'lga_id' => ['required', 'exists:lgas,id'],
            'ward_id' => ['required', 'exists:wards,id'],
            'polling_unit_id' => ['nullable', 'exists:polling_units,id'],
            'photo' => ['nullable', 'string'],
            'referral_code' => ['nullable', 'string', 'size:8'],
        ]);

        $photoPath = null;
        if (! empty($validated['photo'])) {
            $imageParts = explode(';base64,', $validated['photo']);
            if (count($imageParts) == 2) {
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'member_'.time().'_'.uniqid().'.'.$imageType;
                Storage::disk('public')->put('members/photos/'.$fileName, $imageBase64);
                $photoPath = 'members/photos/'.$fileName;
            }
        }

        $latestId = Member::max('id') ?? 0;
        $nextNumber = str_pad((string) ($latestId + 1), 8, '0', STR_PAD_LEFT);
        $membershipNumber = 'SAJ-'.date('Y').'-'.$nextNumber;

        $referredById = null;
        if (! empty($validated['referral_code'])) {
            $referrer = Member::where('phone', 'like', '%'.$validated['referral_code'])->first();
            if ($referrer) {
                $referredById = $referrer->id;
            }
        }

        Member::create([
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
            'referred_by_id' => $referredById,
        ]);

        return redirect()->route('register.success');
    }
}
