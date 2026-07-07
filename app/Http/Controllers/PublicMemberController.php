<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
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
                'state' => $member->state?->name,
                'lga' => $member->lga?->name,
                'ward' => $member->ward?->name,
                'photo_url' => $member->photo_path ? asset('storage/'.$member->photo_path) : null,
                'verified_at' => $member->verified_at ? $member->verified_at->format('M j, Y') : null,
            ],
        ]);
    }
}
