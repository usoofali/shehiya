<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\EscoOfficial;
use App\Models\MovementContent;
use App\Models\State;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function index(Request $request): Response
    {
        $contents = MovementContent::all()->keyBy('key');

        $states = State::with('lgas')->orderBy('name')->get();

        $escoQuery = EscoOfficial::with(['position', 'state', 'lga', 'ward'])
            ->where('status', 'active');

        if ($request->filled('state_id')) {
            $escoQuery->where('state_id', $request->state_id);
        }

        if ($request->filled('lga_id')) {
            $escoQuery->where('lga_id', $request->lga_id);
        }

        if ($request->filled('ward_id')) {
            $escoQuery->where('ward_id', $request->ward_id);
        }

        $escos = $escoQuery->latest()->limit(50)->get();

        $announcementQuery = Announcement::with(['state', 'lga', 'ward']);

        if ($request->filled('state_id') || $request->filled('lga_id') || $request->filled('ward_id')) {
            $announcementQuery->where(function ($query) use ($request) {
                $query->where('target_level', 'national');

                if ($request->filled('state_id')) {
                    $query->orWhere(function ($q) use ($request) {
                        $q->where('target_level', 'state')->where('state_id', $request->state_id);
                    });
                }

                if ($request->filled('lga_id')) {
                    $query->orWhere(function ($q) use ($request) {
                        $q->where('target_level', 'lga')->where('lga_id', $request->lga_id);
                    });
                }

                if ($request->filled('ward_id')) {
                    $query->orWhere(function ($q) use ($request) {
                        $q->where('target_level', 'ward')->where('ward_id', $request->ward_id);
                    });
                }
            });
        }

        $announcements = $announcementQuery->latest()->limit(10)->get();

        return Inertia::render('Welcome', [
            'contents' => $contents,
            'states' => $states,
            'escos' => $escos,
            'announcements' => $announcements,
            'filters' => $request->only(['state_id', 'lga_id', 'ward_id']),
        ]);
    }

    public function showAnnouncement(Announcement $announcement)
    {
        $announcement->load(['publishedBy', 'state', 'lga', 'ward']);

        return Inertia::render('Public/ShowAnnouncement', [
            'announcement' => $announcement,
        ]);
    }
}
