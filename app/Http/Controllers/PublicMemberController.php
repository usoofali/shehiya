<?php

namespace App\Http\Controllers;

use App\Models\EscoOfficial;
use App\Models\Member;
use App\Models\Patron;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicMemberController extends Controller
{
    /**
     * Show the status check page.
     */
    public function showCheckStatus(): Response
    {
        return Inertia::render('Public/CheckStatus');
    }

    /**
     * Handle the status check request via phone number.
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $member = Member::where('phone', $request->phone)->first();

        if (! $member) {
            return back()->with('error', 'No membership record found for this phone number.');
        }

        $coordinator = User::role('Ward Coordinator')
            ->where('ward_id', $member->ward_id)
            ->first();

        return back()->with('memberData', [
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'membership_number' => $member->membership_number,
            'status' => $member->status,
            'phone' => $member->phone,
            'voter_card_path' => $member->voter_card_path,
            'referrals_count' => $member->referrals_count,
            'verified_referrals_count' => $member->verified_referrals_count,
            'referral_badge' => $member->referral_badge,
            'coordinator_name' => $coordinator ? $coordinator->name : null,
            'coordinator_phone' => $coordinator ? $coordinator->email : null,
        ]);
    }

    /**
     * Handle retroactive Voter Card upload for an already registered member.
     */
    public function uploadVoterCard(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'membership_number' => ['required', 'string'],
            'voter_card' => ['required'],
        ]);

        $member = Member::where('phone', $validated['phone'])
            ->where('membership_number', $validated['membership_number'])
            ->first();

        if (! $member) {
            return back()->with('error', 'Authentication failed. Member record not found matching phone and membership number.');
        }

        $voterCardPath = null;
        if ($request->hasFile('voter_card') && $request->file('voter_card')->isValid()) {
            $voterCardPath = $request->file('voter_card')->store('members/voter_cards', 'public');
        } elseif (! empty($validated['voter_card']) && is_string($validated['voter_card'])) {
            $imageParts = explode(';base64,', $validated['voter_card']);
            if (count($imageParts) == 2) {
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'vc_'.time().'_'.uniqid().'.'.$imageType;
                Storage::disk('public')->put('members/voter_cards/'.$fileName, $imageBase64);
                $voterCardPath = 'members/voter_cards/'.$fileName;
            }
        }

        if (empty($voterCardPath)) {
            return back()->withErrors(['voter_card' => 'A valid Voter Card image or scan is required before uploading.'])->withInput();
        }

        if ($member->voter_card_path && Storage::disk('public')->exists($member->voter_card_path)) {
            Storage::disk('public')->delete($member->voter_card_path);
        }

        $member->update([
            'voter_card_path' => $voterCardPath,
        ]);

        $coordinator = User::role('Ward Coordinator')
            ->where('ward_id', $member->ward_id)
            ->first();

        return back()->with('success', 'Your Voter Card has been uploaded and linked to your membership profile successfully!')->with('memberData', [
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'membership_number' => $member->membership_number,
            'status' => $member->status,
            'phone' => $member->phone,
            'voter_card_path' => $member->voter_card_path,
            'referrals_count' => $member->referrals_count,
            'verified_referrals_count' => $member->verified_referrals_count,
            'referral_badge' => $member->referral_badge,
            'coordinator_name' => $coordinator ? $coordinator->name : null,
            'coordinator_phone' => $coordinator ? $coordinator->email : null,
        ]);
    }

    /**
     * Show the printable ID badge page.
     */
    public function showBadge($membership_number): Response
    {
        $member = Member::with(['state', 'lga', 'ward'])
            ->where('membership_number', $membership_number)
            ->firstOrFail();

        return Inertia::render('Public/IdBadge', [
            'member' => [
                'name' => $member->first_name.' '.$member->last_name,
                'membership_number' => $member->membership_number,
                'status' => $member->status,
                'voter_card_path' => $member->voter_card_path,
                'state' => $member->state?->name,
                'lga' => $member->lga?->name,
                'ward' => $member->ward?->name,
                'photo_url' => $member->photo_path ? asset('storage/'.$member->photo_path) : null,
            ],
        ]);
    }

    /**
     * Public route to verify a member via QR code scan.
     */
    public function verify($membership_number): Response
    {
        $member = Member::with(['state', 'lga', 'ward'])
            ->where('membership_number', $membership_number)
            ->firstOrFail();

        return Inertia::render('Public/VerifyMember', [
            'member' => [
                'name' => $member->first_name.' '.$member->last_name,
                'membership_number' => $member->membership_number,
                'status' => $member->status,
                'voter_card_path' => $member->voter_card_path,
                'state' => $member->state?->name,
                'lga' => $member->lga?->name,
                'ward' => $member->ward?->name,
                'photo_url' => $member->photo_path ? asset('storage/'.$member->photo_path) : null,
                'verified_at' => $member->verified_at ? $member->verified_at->format('M j, Y') : null,
            ],
        ]);
    }

    /**
     * Show the printable ID badge page for an EXCO official.
     */
    public function showEscoBadge($id): Response
    {
        $esco = EscoOfficial::with(['position', 'state', 'lga', 'ward', 'pollingUnit'])
            ->findOrFail($id);

        $badgeId = 'EXCO-' . str_pad($esco->id, 5, '0', STR_PAD_LEFT);

        return Inertia::render('Public/EscoBadge', [
            'esco' => [
                'id' => $esco->id,
                'name' => $esco->full_name,
                'badge_id' => $badgeId,
                'position' => $esco->position?->name ?? 'EXCO Official',
                'status' => $esco->status,
                'state' => $esco->state?->name,
                'lga' => $esco->lga?->name,
                'ward' => $esco->ward?->name,
                'polling_unit' => $esco->pollingUnit?->name,
                'photo_url' => $esco->photo_path ? asset('storage/'.$esco->photo_path) : null,
                'appointed_at' => $esco->appointed_at ? $esco->appointed_at->format('M j, Y') : null,
            ],
        ]);
    }

    /**
     * Public route to verify an EXCO official via QR code scan.
     */
    public function verifyEsco($id): Response
    {
        $esco = EscoOfficial::with(['position', 'state', 'lga', 'ward', 'pollingUnit'])
            ->findOrFail($id);

        $badgeId = 'EXCO-' . str_pad($esco->id, 5, '0', STR_PAD_LEFT);

        return Inertia::render('Public/VerifyEsco', [
            'esco' => [
                'id' => $esco->id,
                'name' => $esco->full_name,
                'badge_id' => $badgeId,
                'position' => $esco->position?->name ?? 'EXCO Official',
                'status' => $esco->status,
                'state' => $esco->state?->name,
                'lga' => $esco->lga?->name,
                'ward' => $esco->ward?->name,
                'polling_unit' => $esco->pollingUnit?->name,
                'photo_url' => $esco->photo_path ? asset('storage/'.$esco->photo_path) : null,
                'appointed_at' => $esco->appointed_at ? $esco->appointed_at->format('M j, Y') : null,
            ],
        ]);
    }

    /**
     * Show the printable ID badge page for a Patron / Royal Father / Special Adviser.
     */
    public function showPatronBadge($id): Response
    {
        $patron = Patron::findOrFail($id);

        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $patron->category), 0, 3));
        $badgeId = 'SH-' . ($prefix ?: 'PTN') . '-' . str_pad($patron->id, 4, '0', STR_PAD_LEFT);

        return Inertia::render('Public/PatronBadge', [
            'patron' => [
                'id' => $patron->id,
                'name' => $patron->name,
                'title' => $patron->title,
                'category' => $patron->category,
                'badge_id' => $badgeId,
                'is_active' => $patron->is_active,
                'photo_url' => $patron->photo_path ? asset('storage/'.$patron->photo_path) : null,
            ],
        ]);
    }

    /**
     * Public route to verify a Patron / Royal Father via QR code scan.
     */
    public function verifyPatron($id): Response
    {
        $patron = Patron::findOrFail($id);

        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $patron->category), 0, 3));
        $badgeId = 'SH-' . ($prefix ?: 'PTN') . '-' . str_pad($patron->id, 4, '0', STR_PAD_LEFT);

        return Inertia::render('Public/VerifyPatron', [
            'patron' => [
                'id' => $patron->id,
                'name' => $patron->name,
                'title' => $patron->title,
                'category' => $patron->category,
                'badge_id' => $badgeId,
                'is_active' => $patron->is_active,
                'photo_url' => $patron->photo_path ? asset('storage/'.$patron->photo_path) : null,
            ],
        ]);
    }
}
