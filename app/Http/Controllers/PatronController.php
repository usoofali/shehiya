<?php

namespace App\Http\Controllers;

use App\Models\Patron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PatronController extends Controller
{
    /**
     * Display a listing of patrons.
     */
    public function index(Request $request): Response
    {
        if (! $request->user()->hasRole('Super Administrator')) {
            abort(403, 'You do not have permission to view Patrons management.');
        }

        $query = Patron::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $patrons = $query->orderByRaw("FIELD(category, 'Grand Patron', 'Patron', 'Royal Father')")
            ->orderBy('order_index')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Patrons/Index', [
            'patrons' => $patrons,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    /**
     * Store a newly created patron in storage.
     */
    public function store(Request $request)
    {
        if (! $request->user()->hasRole('Super Administrator')) {
            abort(403, 'You do not have permission to manage Patrons.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:Grand Patron,Patron,Royal Father'],
            'photo' => ['nullable', 'string'],
            'order_index' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $photoPath = null;
        if (! empty($validated['photo'])) {
            $imageParts = explode(';base64,', $validated['photo']);
            if (count($imageParts) == 2) {
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'patron_'.time().'_'.uniqid().'.'.$imageType;
                Storage::disk('public')->put('patrons/photos/'.$fileName, $imageBase64);
                $photoPath = 'patrons/photos/'.$fileName;
            }
        }

        Patron::create([
            'name' => $validated['name'],
            'title' => $validated['title'],
            'category' => $validated['category'],
            'photo_path' => $photoPath,
            'order_index' => $validated['order_index'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', 'Patron added successfully.');
    }

    /**
     * Update the specified patron in storage.
     */
    public function update(Request $request, Patron $patron)
    {
        if (! $request->user()->hasRole('Super Administrator')) {
            abort(403, 'You do not have permission to manage Patrons.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:Grand Patron,Patron,Royal Father'],
            'photo' => ['nullable', 'string'],
            'order_index' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $photoPath = $patron->photo_path;
        if (! empty($validated['photo'])) {
            $imageParts = explode(';base64,', $validated['photo']);
            if (count($imageParts) == 2) {
                // Delete old photo if exists
                if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'patron_'.time().'_'.uniqid().'.'.$imageType;
                Storage::disk('public')->put('patrons/photos/'.$fileName, $imageBase64);
                $photoPath = 'patrons/photos/'.$fileName;
            }
        }

        $patron->update([
            'name' => $validated['name'],
            'title' => $validated['title'],
            'category' => $validated['category'],
            'photo_path' => $photoPath,
            'order_index' => $validated['order_index'] ?? $patron->order_index,
            'is_active' => $validated['is_active'] ?? $patron->is_active,
        ]);

        return back()->with('success', 'Patron updated successfully.');
    }

    /**
     * Remove the specified patron from storage.
     */
    public function destroy(Request $request, Patron $patron)
    {
        if (! $request->user()->hasRole('Super Administrator')) {
            abort(403, 'You do not have permission to manage Patrons.');
        }

        if ($patron->photo_path && Storage::disk('public')->exists($patron->photo_path)) {
            Storage::disk('public')->delete($patron->photo_path);
        }

        $patron->delete();

        return back()->with('success', 'Patron removed successfully.');
    }
}
