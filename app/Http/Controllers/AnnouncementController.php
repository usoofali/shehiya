<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\State;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(Request $request): Response
    {
        $announcements = Announcement::with(['publishedBy', 'state'])
            ->visibleTo($request->user())
            ->latest()
            ->paginate(15);

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements,
            'states' => State::all(),
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->user()->can('create announcements')) {
            abort(403, 'You do not have permission to publish announcements.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', 'in:notice,meeting,update'],
            'image' => ['nullable', 'image', 'max:2048'], // Allow up to 2MB for standard image uploads via multipart
            'target_level' => ['required', 'in:national,state,lga,ward'],
            'state_id' => ['nullable', 'required_if:target_level,state', 'exists:states,id'],
            'lga_id' => ['nullable', 'exists:lgas,id'],
            'ward_id' => ['nullable', 'exists:wards,id'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements/images', 'public');
        }

        Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'type' => $validated['type'],
            'target_level' => $validated['target_level'],
            'state_id' => $validated['state_id'] ?? null,
            'lga_id' => $validated['lga_id'] ?? null,
            'ward_id' => $validated['ward_id'] ?? null,
            'published_by_user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Announcement published successfully.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        if (! $request->user()->can('create announcements') && $announcement->published_by_user_id !== $request->user()->id) {
            abort(403, 'You do not have permission to edit this announcement.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', 'in:notice,meeting,update'],
            'image' => ['nullable', 'image', 'max:2048'],
            'target_level' => ['required', 'in:national,state,lga,ward'],
            'state_id' => ['nullable', 'required_if:target_level,state', 'exists:states,id'],
            'lga_id' => ['nullable', 'exists:lgas,id'],
            'ward_id' => ['nullable', 'exists:wards,id'],
        ]);

        $imagePath = $announcement->image_path;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements/images', 'public');
        }

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'type' => $validated['type'],
            'target_level' => $validated['target_level'],
            'state_id' => $validated['state_id'] ?? null,
            'lga_id' => $validated['lga_id'] ?? null,
            'ward_id' => $validated['ward_id'] ?? null,
        ]);

        return back()->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        if (! $request->user()->can('delete announcements') && $announcement->published_by_user_id !== $request->user()->id) {
            abort(403, 'You do not have permission to delete this announcement.');
        }

        $announcement->delete();

        return back()->with('success', 'Announcement deleted successfully.');
    }
}
