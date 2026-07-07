<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PollingUnit;
use App\Models\State;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAccess();

        $user = $request->user();
        $query = User::with(['roles', 'state', 'lga', 'ward', 'pollingUnit']);

        // Scope users based on hierarchy
        if ($user->hasRole('National Administrator')) {
            $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['State Coordinator', 'LGA Coordinator', 'Ward Coordinator', 'Polling Unit Coordinator']));
        } elseif ($user->hasRole('State Coordinator')) {
            $query->where('state_id', $user->state_id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['LGA Coordinator', 'Ward Coordinator', 'Polling Unit Coordinator']));
        } elseif ($user->hasRole('LGA Coordinator')) {
            $query->where('lga_id', $user->lga_id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Ward Coordinator', 'Polling Unit Coordinator']));
        } elseif ($user->hasRole('Ward Coordinator')) {
            $query->where('ward_id', $user->ward_id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Polling Unit Coordinator']));
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->role($role);
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        // Scope available roles for creation
        $rolesQuery = Role::orderBy('name');
        if ($user->hasRole('National Administrator')) {
            $rolesQuery->where('name', 'State Coordinator');
        } elseif ($user->hasRole('State Coordinator')) {
            $rolesQuery->where('name', 'LGA Coordinator');
        } elseif ($user->hasRole('LGA Coordinator')) {
            $rolesQuery->where('name', 'Ward Coordinator');
        } elseif ($user->hasRole('Ward Coordinator')) {
            $rolesQuery->where('name', 'Polling Unit Coordinator');
        }
        $roles = $rolesQuery->get();

        // Scope geographical locations based on user's jurisdiction
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
        $states = $statesQuery->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'states' => $states,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
            'state_id' => ['nullable', 'exists:states,id'],
            'lga_id' => ['nullable', 'exists:lgas,id'],
            'ward_id' => ['nullable', 'exists:wards,id'],
            'polling_unit_id' => ['nullable', 'exists:polling_units,id'],
        ]);

        $creator = $request->user();
        $this->enforceWaterfallCreationRules($creator, $validated);

        if (! empty($validated['ward_id']) && ! empty($validated['lga_id'])) {
            if (! Ward::where('id', $validated['ward_id'])->where('lga_id', $validated['lga_id'])->exists()) {
                return back()->withErrors(['ward_id' => 'The selected ward does not belong to the selected LGA.']);
            }
        }
        if (! empty($validated['polling_unit_id']) && ! empty($validated['ward_id'])) {
            if (! PollingUnit::where('id', $validated['polling_unit_id'])->where('ward_id', $validated['ward_id'])->exists()) {
                return back()->withErrors(['polling_unit_id' => 'The selected polling unit does not belong to the selected ward.']);
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'state_id' => $validated['state_id'] ?? null,
            'lga_id' => $validated['lga_id'] ?? null,
            'ward_id' => $validated['ward_id'] ?? null,
            'polling_unit_id' => $validated['polling_unit_id'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        return back()->with('success', "Coordinator account created for {$user->name}.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAccess();
        $creator = $request->user();
        $this->enforceTargetUserJurisdiction($creator, $user);

        $validated = $request->validate([
            'role' => ['required', 'exists:roles,name'],
            'state_id' => ['nullable', 'exists:states,id'],
            'lga_id' => ['nullable', 'exists:lgas,id'],
            'ward_id' => ['nullable', 'exists:wards,id'],
            'polling_unit_id' => ['nullable', 'exists:polling_units,id'],
        ]);

        $this->enforceWaterfallCreationRules($creator, $validated);

        if (! empty($validated['ward_id']) && ! empty($validated['lga_id'])) {
            if (! Ward::where('id', $validated['ward_id'])->where('lga_id', $validated['lga_id'])->exists()) {
                return back()->withErrors(['ward_id' => 'The selected ward does not belong to the selected LGA.']);
            }
        }
        if (! empty($validated['polling_unit_id']) && ! empty($validated['ward_id'])) {
            if (! PollingUnit::where('id', $validated['polling_unit_id'])->where('ward_id', $validated['ward_id'])->exists()) {
                return back()->withErrors(['polling_unit_id' => 'The selected polling unit does not belong to the selected ward.']);
            }
        }

        $user->syncRoles([$validated['role']]);
        $user->update([
            'state_id' => $validated['state_id'] ?? null,
            'lga_id' => $validated['lga_id'] ?? null,
            'ward_id' => $validated['ward_id'] ?? null,
            'polling_unit_id' => $validated['polling_unit_id'] ?? null,
        ]);

        return back()->with('success', "{$user->name}'s role and jurisdiction updated.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAccess();
        $creator = $request->user();

        if ($user->id === $creator->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $this->enforceTargetUserJurisdiction($creator, $user);

        $user->delete();

        return back()->with('success', 'Coordinator account deleted.');
    }

    protected function authorizeAccess(): void
    {
        $user = request()->user();
        if (! $user || ! $user->hasAnyRole(['Super Administrator', 'National Administrator', 'State Coordinator', 'LGA Coordinator', 'Ward Coordinator'])) {
            abort(403, 'You do not have permission to manage coordinator accounts.');
        }
    }

    protected function enforceWaterfallCreationRules(User $creator, array &$validated): void
    {
        if ($creator->hasRole('Super Administrator')) {
            return;
        }

        if ($creator->hasRole('National Administrator')) {
            if ($validated['role'] !== 'State Coordinator') {
                abort(403, 'National Administrators can only create State Coordinators.');
            }
        } elseif ($creator->hasRole('State Coordinator')) {
            if ($validated['role'] !== 'LGA Coordinator') {
                abort(403, 'State Coordinators can only create LGA Coordinators.');
            }
            $validated['state_id'] = $creator->state_id;
        } elseif ($creator->hasRole('LGA Coordinator')) {
            if ($validated['role'] !== 'Ward Coordinator') {
                abort(403, 'LGA Coordinators can only create Ward Coordinators.');
            }
            $validated['state_id'] = $creator->state_id;
            $validated['lga_id'] = $creator->lga_id;
        } elseif ($creator->hasRole('Ward Coordinator')) {
            if ($validated['role'] !== 'Polling Unit Coordinator') {
                abort(403, 'Ward Coordinators can only create Polling Unit Coordinators.');
            }
            $validated['state_id'] = $creator->state_id;
            $validated['lga_id'] = $creator->lga_id;
            $validated['ward_id'] = $creator->ward_id;
        } else {
            abort(403, 'You do not have permission to create coordinators.');
        }
    }

    protected function enforceTargetUserJurisdiction(User $creator, User $target): void
    {
        if ($creator->hasRole('Super Administrator')) {
            return;
        }

        if ($creator->hasRole('National Administrator')) {
            if (! $target->hasAnyRole(['State Coordinator', 'LGA Coordinator', 'Ward Coordinator', 'Polling Unit Coordinator'])) {
                abort(403, 'You cannot modify this user.');
            }
        } elseif ($creator->hasRole('State Coordinator')) {
            if ($target->state_id !== $creator->state_id || ! $target->hasAnyRole(['LGA Coordinator', 'Ward Coordinator', 'Polling Unit Coordinator'])) {
                abort(403, 'You cannot modify a user outside your state or hierarchy.');
            }
        } elseif ($creator->hasRole('LGA Coordinator')) {
            if ($target->lga_id !== $creator->lga_id || ! $target->hasAnyRole(['Ward Coordinator', 'Polling Unit Coordinator'])) {
                abort(403, 'You cannot modify a user outside your LGA or hierarchy.');
            }
        } elseif ($creator->hasRole('Ward Coordinator')) {
            if ($target->ward_id !== $creator->ward_id || ! $target->hasRole('Polling Unit Coordinator')) {
                abort(403, 'You cannot modify a user outside your ward or hierarchy.');
            }
        } else {
            abort(403, 'Unauthorized action.');
        }
    }
}
