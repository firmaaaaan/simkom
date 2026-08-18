<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => $request->password,
        ]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }

    public function resetToDefault(Request $request)
    {
        $request->validate([
            'reset_confirmation' => ['required', 'accepted'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'password')),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Kata sandi berhasil direset ke default. Silakan login kembali dengan password default.');
    }
}
