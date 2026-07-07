<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        $this->authorize();

        $roles = Role::with('permissions')->withCount('users')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (! empty($validated['permissions'])) {
            $role->givePermissionTo($validated['permissions']);
        }

        return back()->with('success', "Role '{$role->name}' created successfully.");
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize();

        if (in_array($role->name, ['Super Administrator'])) {
            return back()->with('error', 'The Super Administrator role cannot be modified.');
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('success', "Role '{$role->name}' permissions updated.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize();

        if (in_array($role->name, ['Super Administrator', 'National Administrator', 'State Coordinator', 'LGA Coordinator', 'Ward Coordinator', 'Polling Unit Coordinator'])) {
            return back()->with('error', 'Core system roles cannot be deleted.');
        }

        $role->delete();

        return back()->with('success', "Role '{$role->name}' deleted.");
    }

    protected function authorize(): void
    {
        if (! request()->user()?->hasRole('Super Administrator')) {
            abort(403, 'Only Super Administrators can manage roles.');
        }
    }
}
