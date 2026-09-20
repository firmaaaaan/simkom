<?php

namespace App\Http\Controllers;

use App\Exports\RoleExport;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->orderBy('label')->get();

        return view('roles.index', compact('roles'));
    }

    public function export()
    {
        return Excel::download(
            new RoleExport(Role::query()->orderBy('label')),
            'role-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function create()
    {
        $permissions = Permission::orderBy('label')->get();

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRole($request);

        $role = Role::create([
            'name' => $validated['name'],
            'label' => $validated['label'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role "' . $role->label . '" berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::orderBy('label')->get();

        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $this->validateRole($request, $role);
        $permissionIds = $validated['permissions'] ?? [];

        if ($error = $this->lockoutError($role, $permissionIds)) {
            return back()->withInput()->withErrors(['permissions' => $error]);
        }

        $role->update([
            'name' => $validated['name'],
            'label' => $validated['label'],
        ]);

        $role->permissions()->sync($permissionIds);

        return redirect()->route('roles.index')->with('success', 'Role "' . $role->label . '" berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        $userCount = $role->users()->count();

        if ($userCount > 0) {
            return back()->withErrors([
                'error' => 'Role "' . $role->label . '" masih dipakai oleh ' . $userCount . ' user. '
                    . 'Pindahkan user tersebut ke role lain sebelum menghapus role ini.',
            ]);
        }

        // Tidak perlu cek lockout di sini: pemakai izin "Kelola Role" pasti punya
        // role lain yang memegang izin tersebut (role-nya sendiri), kecuali role
        // itu yang sedang dihapus — dan itu sudah tertahan oleh cek user di atas.
        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role "' . $role->label . '" berhasil dihapus.');
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ],
            'label' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,id'],
        ], [
            'name.alpha_dash' => 'Nama role hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'name.unique' => 'Nama role tersebut sudah dipakai.',
        ]);
    }

    /**
     * Cegah perubahan yang membuat tidak ada satu pun role pemegang izin
     * "Kelola Role" (yang akan mengunci semua orang dari menu Role).
     */
    private function lockoutError(Role $role, array $newPermissionIds): ?string
    {
        $manageRolesId = Permission::where('name', 'manage-roles')->value('id');

        if (! $manageRolesId || in_array($manageRolesId, $newPermissionIds)) {
            return null;
        }

        $role->loadMissing('permissions');

        if (! $role->permissions->contains('id', $manageRolesId)) {
            return null;
        }

        $otherHolderExists = Role::whereKeyNot($role->id)
            ->whereHas('permissions', fn ($query) => $query->where('name', 'manage-roles'))
            ->exists();

        return $otherHolderExists
            ? null
            : 'Role ini satu-satunya yang memiliki izin "Kelola Role". Berikan izin tersebut ke role lain terlebih dahulu agar tidak ada yang terkunci dari menu Role.';
    }
}
