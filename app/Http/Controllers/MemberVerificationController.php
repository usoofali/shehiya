<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberVerification;
use Illuminate\Http\Request;

class MemberVerificationController extends Controller
{
    public function store(Request $request, Member $member)
    {
        if (! $request->user()->can('verify members')) {
            abort(403, 'You do not have permission to verify members.');
        }

        if (! Member::accessibleBy($request->user())->where('id', $member->id)->exists()) {
            abort(403, 'Unauthorized jurisdiction access.');
        }

        $validated = $request->validate([
            'new_status' => ['required', 'in:verified,rejected'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ]);

        $previousStatus = $member->status;

        $member->update([
            'status' => $validated['new_status'],
        ]);

        MemberVerification::create([
            'member_id' => $member->id,
            'verified_by_user_id' => $request->user()->id,
            'previous_status' => $previousStatus,
            'new_status' => $validated['new_status'],
            'comments' => $validated['comments'] ?? null,
        ]);

        return back()->with('success', "Member status updated to {$validated['new_status']} successfully.");
    }

    public function bulkStore(Request $request)
    {
        if (! $request->user()->can('verify members')) {
            abort(403, 'You do not have permission to verify members.');
        }

        $validated = $request->validate([
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['required', 'integer'],
            'new_status' => ['required', 'in:verified,rejected'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ]);

        $members = Member::accessibleBy($request->user())
            ->whereIn('id', $validated['member_ids'])
            ->get();

        $count = 0;
        foreach ($members as $member) {
            if ($member->status !== $validated['new_status']) {
                $previousStatus = $member->status;

                $member->update([
                    'status' => $validated['new_status'],
                ]);

                MemberVerification::create([
                    'member_id' => $member->id,
                    'verified_by_user_id' => $request->user()->id,
                    'previous_status' => $previousStatus,
                    'new_status' => $validated['new_status'],
                    'comments' => $validated['comments'] ?? null,
                ]);

                $count++;
            }
        }

        return back()->with('success', "Bulk status update completed: {$count} member(s) updated to {$validated['new_status']} successfully.");
    }
}
