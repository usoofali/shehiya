<?php

namespace App\Http\Controllers;

use App\Models\MovementContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MovementContentController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        abort_if(! auth()->user()->hasRole('Super Administrator'), 403, 'Only Super Administrators can manage website content.');
    }

    public function index(): Response
    {
        $this->authorizeSuperAdmin();

        $contents = MovementContent::orderBy('key')->get();

        return Inertia::render('Content/Index', [
            'contents' => $contents,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255', 'unique:movement_contents,key'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        MovementContent::create($validated);

        return redirect()->back()->with('success', 'Content block created successfully.');
    }

    public function update(Request $request, MovementContent $content): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        $content->update($validated);

        return redirect()->back()->with('success', 'Content block updated successfully.');
    }

    public function destroy(MovementContent $content): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $content->delete();

        return redirect()->back()->with('success', 'Content block deleted successfully.');
    }
}
