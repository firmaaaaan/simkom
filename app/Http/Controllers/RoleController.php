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

        $role->syncPermissions($validated['permissions'] ?? []);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('roles.create')
                ->with('success', 'Role "' . $role->label . '" berhasil ditambahkan. Silakan isi form untuk data berikutnya.');
        }

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

        $role->syncPermissions($permissionIds);

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

        // Tidak perlu cek lockout di sini: role pemegang izin "Lihat Role" +
        // "Ubah Role"/"Tambah Role" pasti dipakai user, sehingga sudah
        // tertahan oleh cek user di atas.
        $role->syncPermissions([]);
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
     * Cegah perubahan yang membuat tidak ada satu pun role yang bisa
     * mengakses sekaligus mengelola menu Role ("Lihat Role" +
     * "Ubah/Tambah Role") — semua orang akan terkunci dari menu Role.
     */
    private function lockoutError(Role $role, array $newPermissionIds): ?string
    {
        $viewId = Permission::where('name', 'view-roles')->value('id');
        $fixIds = Permission::whereIn('name', ['edit-roles', 'create-roles'])->pluck('id');

        if (! $viewId || $fixIds->isEmpty()) {
            return null;
        }

        $canRecover = function (array $ids) use ($viewId, $fixIds): bool {
            return in_array($viewId, $ids, true)
                && collect($ids)->intersect($fixIds)->isNotEmpty();
        };

        $role->loadMissing('permissions');
        $currentIds = $role->permissions->pluck('id')->all();

        $otherRecovererExists = Role::whereKeyNot($role->id)
            ->with('permissions:id')
            ->get()
            ->contains(fn ($other) => $canRecover($other->permissions->pluck('id')->all()));

        if ($canRecover($currentIds) && ! $otherRecovererExists && ! $canRecover($newPermissionIds)) {
            return 'Role ini satu-satunya yang bisa mengakses & mengelola menu Role (izin "Lihat Role" + "Ubah Role"/"Tambah Role"). '
                .'Berikan kombinasi izin tersebut ke role lain terlebih dahulu agar tidak ada yang terkunci dari menu Role.';
        }

        return null;
    }
}
