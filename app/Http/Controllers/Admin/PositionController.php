<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function index(): Response
    {
        $this->authorize();

        $positions = Position::withCount('escoOfficials')->orderBy('name')->get();

        return Inertia::render('Admin/Positions/Index', [
            'positions' => $positions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:positions,name'],
        ]);

        Position::create(['name' => $validated['name']]);

        return back()->with('success', 'EXCO Position created successfully.');
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $this->authorize();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:positions,name,'.$position->id],
        ]);

        $position->update(['name' => $validated['name']]);

        return back()->with('success', 'EXCO Position updated successfully.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $this->authorize();

        if ($position->escoOfficials()->exists()) {
            return back()->with('error', 'Cannot delete a position that is currently assigned to EXCO officials.');
        }

        $position->delete();

        return back()->with('success', 'EXCO Position deleted successfully.');
    }

    protected function authorize(): void
    {
        if (! request()->user()?->hasRole('Super Administrator')) {
            abort(403, 'Only Super Administrators can manage positions.');
        }
    }
}
