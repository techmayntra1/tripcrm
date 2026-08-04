<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('name')->get();
        $modules = Permission::getModules();
        $totalModules = count($modules);
        $activeCount = Role::count();
        $trashedCount = Role::onlyTrashed()->count();
        return view('admin.roles.index', compact('roles', 'modules', 'totalModules', 'activeCount', 'trashedCount'));
    }

    public function trashed()
    {
        $roles = Role::onlyTrashed()->withCount('users')->with('permissions')->orderBy('name')->get();
        $modules = Permission::getModules();
        $totalModules = count($modules);
        $activeCount = Role::count();
        return view('admin.roles.trashed', compact('roles', 'modules', 'totalModules', 'activeCount'));
    }

    public function restore($id)
    {
        $role = Role::onlyTrashed()->findOrFail($id);
        $role->restore();
        return redirect()->route('admin.roles.trashed')->with('success', 'Role restored successfully.');
    }

    public function create()
    {
        $modules = Permission::getModules();
        $actions = Permission::getActions();
        $permissions = Permission::all()->groupBy('module');

        return view('admin.roles.create', compact('modules', 'actions', 'permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:150',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_default' => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $modules = Permission::getModules();
        $actions = Permission::getActions();
        $permissions = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'modules', 'actions', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:150',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $updateData = [
            'description' => $validated['description'] ?? null,
        ];

        if ($role->slug !== 'admin') {
            $updateData['name'] = $validated['name'];
            $updateData['slug'] = Str::slug($validated['name']);
        }

        $role->update($updateData);

        if ($role->slug !== 'admin') {
            $role->permissions()->sync($validated['permissions'] ?? []);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->slug === 'admin') {
            return redirect()->route('admin.roles.index')->with('error', 'Admin role cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')->with('error', 'Cannot delete role with assigned users.');
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
