<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('activityLogs');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        $summary = [
            'total' => User::count(),
            'logs' => ActivityLog::count(),
        ];

        return view('admin.user.index', compact('users', 'summary'));
    }

    public function create()
    {
        return view('admin.user.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'method' => 'POST',
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => 'Menambahkan akun: ' . $user->name . ' (' . $user->email . ')',
            'properties' => ['created_user_id' => $user->id],
            'created_at' => now(),
        ]);

        return redirect()->route('admin.user.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function toggleActive(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'method' => 'POST',
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => ($user->is_active ? 'Mengaktifkan akun: ' : 'Menonaktifkan akun: ') . $user->name . ' (' . $user->email . ')',
            'properties' => ['toggled_user_id' => $user->id, 'is_active' => $user->is_active],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Status akun ' . $user->name . ' berhasil diperbarui.');
    }

    public function edit(User $user)
    {
        return view('admin.user.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'method' => 'PUT',
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => 'Memperbarui akun: ' . $user->name . ' (' . $user->email . ')',
            'properties' => ['updated_user_id' => $user->id],
            'created_at' => now(),
        ]);

        return redirect()->route('admin.user.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $deletedName = $user->name . ' (' . $user->email . ')';
        $user->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'method' => 'DELETE',
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => 'Menghapus akun: ' . $deletedName,
            'properties' => ['deleted_user_id' => $user->id],
            'created_at' => now(),
        ]);

        return redirect()->route('admin.user.index')->with('success', 'Akun berhasil dihapus.');
    }
}
