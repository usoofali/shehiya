<?php

namespace App\Http\Controllers;

use App\Models\EscoOfficial;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $memberQuery = Member::accessibleBy($user);

        $totalMembers = (clone $memberQuery)->count();
        $verifiedMembers = (clone $memberQuery)->where('status', 'verified')->count();
        $pendingMembers = (clone $memberQuery)->where('status', 'pending')->count();
        $totalEsco = EscoOfficial::accessibleBy($user)->count();

        // Geographical Breakdown
        $membersByState = (clone $memberQuery)
            ->join('states', 'members.state_id', '=', 'states.id')
            ->select('states.name', DB::raw('count(*) as total'))
            ->groupBy('states.name')
            ->get();

        $membersByLga = (clone $memberQuery)
            ->join('lgas', 'members.lga_id', '=', 'lgas.id')
            ->select('lgas.name', DB::raw('count(*) as total'))
            ->groupBy('lgas.name')
            ->limit(10)
            ->get();

        $recentMembers = (clone $memberQuery)
            ->with(['state', 'lga', 'ward'])
            ->latest('registered_at')
            ->limit(6)
            ->get();

        $user->load(['state', 'lga', 'ward', 'pollingUnit']);
        $jurisdictionLabel = 'National';
        if ($user->hasRole('State Coordinator')) {
            $jurisdictionLabel = $user->state?->name ?? 'State';
        } elseif ($user->hasRole('LGA Coordinator')) {
            $jurisdictionLabel = ($user->lga?->name ?? 'LGA').' ('.($user->state?->name ?? '').')';
        } elseif ($user->hasRole('Ward Coordinator')) {
            $jurisdictionLabel = ($user->ward?->name ?? 'Ward').' ('.($user->lga?->name ?? '').')';
        } elseif ($user->hasRole('Polling Unit Coordinator')) {
            $jurisdictionLabel = ($user->pollingUnit?->name ?? 'PU').' ('.($user->ward?->name ?? '').')';
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_members' => $totalMembers,
                'verified_members' => $verifiedMembers,
                'pending_members' => $pendingMembers,
                'total_esco' => $totalEsco,
            ],
            'charts' => [
                'by_state' => $membersByState,
                'by_lga' => $membersByLga,
            ],
            'recentMembers' => $recentMembers,
            'userInfo' => [
                'role' => $user->getRoleNames()->first() ?? 'Member',
                'jurisdiction' => $jurisdictionLabel,
            ],
        ]);
    }
}
