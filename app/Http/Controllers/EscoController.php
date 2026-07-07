<?php

namespace App\Http\Controllers;

use App\Models\EscoOfficial;
use App\Models\Position;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EscoController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = EscoOfficial::with(['position', 'state', 'lga', 'ward', 'pollingUnit'])
            ->accessibleBy($user);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhereHas('position', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($stateId = $request->input('state_id')) {
            $query->where('state_id', $stateId);
        }

        $officials = $query->latest('appointed_at')->paginate(15)->withQueryString();

        $statesQuery = State::with('lgas.wards');
        if ($user->hasRole('State Coordinator') && $user->state_id) {
            $statesQuery->where('id', $user->state_id);
        } elseif ($user->hasRole('LGA Coordinator') && $user->lga_id) {
            $statesQuery->where('id', $user->state_id)
                ->with(['lgas' => fn ($q) => $q->where('id', $user->lga_id)->with('wards')]);
        } elseif ($user->hasRole('Ward Coordinator') && $user->ward_id) {
            $statesQuery->where('id', $user->state_id)
                ->with(['lgas' => fn ($q) => $q->where('id', $user->lga_id)->with(['wards' => fn ($wq) => $wq->where('id', $user->ward_id)])]);
        }

        return Inertia::render('Esco/Index', [
            'officials' => $officials,
            'filters' => $request->only(['search', 'state_id']),
            'states' => $statesQuery->get(),
            'positions' => Position::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->user()->can('manage esco')) {
            abort(403, 'You do not have permission to manage EXCO officials.');
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'position_id' => ['required', 'exists:positions,id'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'photo' => ['nullable', 'string'],
            'state_id' => ['nullable', 'exists:states,id'],
            'lga_id' => ['nullable', 'exists:lgas,id'],
            'ward_id' => ['nullable', 'exists:wards,id'],
            'polling_unit_id' => ['nullable', 'exists:polling_units,id'],
            'appointed_at' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->enforceDownwardEscoRules($request->user(), $validated);

        $photoPath = null;
        if (! empty($validated['photo'])) {
            $imageParts = explode(';base64,', $validated['photo']);
            if (count($imageParts) == 2) {
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'esco_'.time().'_'.uniqid().'.'.$imageType;
                Storage::disk('public')->put('escos/photos/'.$fileName, $imageBase64);
                $photoPath = 'escos/photos/'.$fileName;
            }
        }

        EscoOfficial::create([
            'full_name' => $validated['full_name'],
            'position_id' => $validated['position_id'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'photo_path' => $photoPath,
            'state_id' => $validated['state_id'] ?? null,
            'lga_id' => $validated['lga_id'] ?? null,
            'ward_id' => $validated['ward_id'] ?? null,
            'polling_unit_id' => $validated['polling_unit_id'] ?? null,
            'appointed_at' => $validated['appointed_at'],
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'EXCO official added successfully.');
    }

    public function destroy(Request $request, EscoOfficial $esco)
    {
        if (! $request->user()->can('manage esco')) {
            abort(403, 'You do not have permission to manage EXCO officials.');
        }

        if (! EscoOfficial::accessibleBy($request->user())->where('id', $esco->id)->exists()) {
            abort(403, 'Unauthorized jurisdiction access.');
        }

        $esco->delete();

        return back()->with('success', 'EXCO official removed successfully.');
    }

    private function enforceDownwardEscoRules($user, array $validated): void
    {
        if ($user->hasRole(['Super Administrator', 'National Administrator'])) {
            return;
        }

        if ($user->hasRole('State Coordinator')) {
            if (empty($validated['state_id']) || $validated['state_id'] != $user->state_id) {
                abort(403, 'State Coordinators can only appoint EXCO officials within their assigned State.');
            }

            return;
        }

        if ($user->hasRole('LGA Coordinator')) {
            if (empty($validated['state_id']) || $validated['state_id'] != $user->state_id ||
                empty($validated['lga_id']) || $validated['lga_id'] != $user->lga_id) {
                abort(403, 'LGA Coordinators can only appoint EXCO officials within their assigned LGA.');
            }

            return;
        }

        if ($user->hasRole('Ward Coordinator')) {
            if (empty($validated['state_id']) || $validated['state_id'] != $user->state_id ||
                empty($validated['lga_id']) || $validated['lga_id'] != $user->lga_id ||
                empty($validated['ward_id']) || $validated['ward_id'] != $user->ward_id) {
                abort(403, 'Ward Coordinators can only appoint EXCO officials within their assigned Ward.');
            }

            return;
        }

        abort(403, 'You do not have permission to appoint EXCO officials.');
    }
}
