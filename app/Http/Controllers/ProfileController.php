<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function updateEmail(Request $request)
    {
        $validated = $request->validateWithBag('email', [
            // Konfirmasi password saat ini wajib sebelum email boleh diganti
            'current_password' => 'required|current_password:web',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
        ]);

        $request->user()->update(['email' => $validated['email']]);

        return back()->with('success', 'Email berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('password', [
            // Konfirmasi password saat ini wajib sebelum password boleh diganti
            'current_password' => 'required|current_password:web',
            'password' => 'required|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Regenerasi session agar sesi lama tidak bisa dipakai setelah ganti password
        $request->session()->regenerate();

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
