<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->with('roles')
            ->when(request('search'), fn ($query, $search) => $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when(request('role'), fn ($query, $role) => $query->role($role))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('management.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('management.users.form', [
            'managedUser' => new User(),
            'roles' => Role::withCount('permissions')->orderBy('name')->get(),
            'selectedRoles' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedUser($request);
        $roles = $validated['roles'] ?? [];
        unset($validated['roles']);

        $user = User::create($validated);
        $user->syncRoles($roles);

        return redirect()->route('management.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('management.users.form', [
            'managedUser' => $user->load('roles'),
            'roles' => Role::withCount('permissions')->orderBy('name')->get(),
            'selectedRoles' => $user->roles->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $this->validatedUser($request, $user);
        $roles = $validated['roles'] ?? [];
        unset($validated['roles']);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->syncRoles($roles);

        return redirect()->route('management.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()?->is($user)) {
            return back()->with('error', 'User yang sedang login tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('management.users.index')->with('success', 'User berhasil dihapus.');
    }

    private function validatedUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);
    }
}
