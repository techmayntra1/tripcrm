<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->orderBy('name')->get();
        $activeCount = User::count();
        $trashedCount = User::onlyTrashed()->count();
        return view('admin.users.index', compact('users', 'activeCount', 'trashedCount'));
    }

    public function trashed()
    {
        $users = User::onlyTrashed()->with('role')->orderBy('name')->get();
        $activeCount = User::count();
        return view('admin.users.trashed', compact('users', 'activeCount'));
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return redirect()->route('admin.users.trashed')->with('success', 'User restored successfully.');
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:30',
            'email' => 'required|email|max:100|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role_id' => 'required|exists:roles,id',
        ]);

        $trashedUser = User::onlyTrashed()->where('email', $validated['email'])->first();

        if ($trashedUser) {
            $trashedUser->restore();
            $trashedUser->update([
                'name' => $validated['name'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
            ]);
        } else {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:30',
            'email' => 'required|email|max:100|unique:users,email,' . $user->id . ',id,deleted_at,NULL',
            'role_id' => 'required|exists:roles,id',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
        ];

        // Admin can optionally reset this user's password. Leave blank to keep unchanged.
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }

        $adminRole = Role::where('slug', 'admin')->first();
        if ($user->role_id === $adminRole?->id) {
            $adminCount = User::where('role_id', $adminRole->id)->count();
            if ($adminCount <= 1) {
                return redirect()->route('admin.users.index')->with('error', 'Cannot delete the last admin user.');
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
