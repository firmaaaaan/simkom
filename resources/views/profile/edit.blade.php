@extends('layouts.app')

@section('title', 'Profil Akun')
@section('header', 'Profil Akun')

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- INFO AKUN --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-green-600 rounded-full flex items-center justify-center text-white text-xl font-semibold flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-base font-semibold text-gray-800 truncate">{{ $user->name }}</p>
                <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                <span class="inline-block mt-1 px-2 py-0.5 bg-green-50 text-green-700 text-xs font-medium rounded-full">{{ $user->role_label }}</span>
            </div>
        </div>
    </div>

    {{-- GANTI EMAIL --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Ganti Email</h2>
        <p class="text-sm text-gray-500 mb-6">Konfirmasi password saat ini untuk mengganti email akun.</p>

        @include('profile.partials.form-errors', ['bag' => 'email'])

        <form action="{{ route('profile.update-email') }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="email_current" class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini <span class="text-red-500">*</span></label>
                @include('profile.partials.password-input', ['name' => 'current_password', 'id' => 'email_current', 'autocomplete' => 'current-password', 'placeholder' => 'Masukkan password saat ini'])
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Baru <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                    placeholder="nama@contoh.com">
            </div>

            <div class="flex items-center gap-3 pt-2 flex-wrap">
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Simpan Email
                </button>
                <p class="text-xs text-gray-400">Email digunakan untuk login.</p>
            </div>
        </form>
    </div>

    {{-- GANTI PASSWORD --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Ganti Password</h2>
        <p class="text-sm text-gray-500 mb-6">Password minimal 8 karakter. Konfirmasi password saat ini untuk mengganti password.</p>

        @include('profile.partials.form-errors', ['bag' => 'password'])

        <form action="{{ route('profile.update-password') }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="password_current" class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini <span class="text-red-500">*</span></label>
                @include('profile.partials.password-input', ['name' => 'current_password', 'id' => 'password_current', 'autocomplete' => 'current-password', 'placeholder' => 'Masukkan password saat ini'])
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-red-500">*</span></label>
                    <div x-data="passwordStrength" class="relative">
                        <input type="password" :type="show ? 'text' : 'password'" name="password" id="password" required minlength="8" autocomplete="new-password" x-model="pw"
                            class="w-full px-4 py-2.5 pr-11 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="Minimal 8 karakter">
                        <button type="button" @click="show = !show" tabindex="-1"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition-colors"
                            :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                            @include('profile.partials.eye-icons')
                        </button>

                        {{-- Indikator kekuatan password --}}
                        <div x-show="pw" x-cloak class="mt-2.5" aria-live="polite">
                            <div class="flex gap-1.5">
                                <template x-for="i in 4" :key="i">
                                    <div class="h-1.5 flex-1 rounded-full transition-colors duration-200"
                                        :class="barClass(i)"></div>
                                </template>
                            </div>
                            <p class="text-xs mt-1.5" :class="textClass()">
                                Kekuatan: <span class="font-semibold" x-text="label()"></span>
                            </p>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                    @include('profile.partials.password-input', ['name' => 'password_confirmation', 'id' => 'password_confirmation', 'autocomplete' => 'new-password', 'placeholder' => 'Ulangi password baru'])
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('passwordStrength', () => ({
            show: false,
            pw: '',
            get score() {
                let s = 0;
                if (this.pw.length >= 8) s++;
                if (/[a-z]/.test(this.pw)) s++;
                if (/[A-Z]/.test(this.pw)) s++;
                if (/[0-9]/.test(this.pw)) s++;
                if (/[^A-Za-z0-9]/.test(this.pw)) s++;
                if (this.pw.length >= 12) s++;
                return Math.min(s, 6);
            },
            get strength() {
                if (!this.pw) return null;
                const s = this.score;
                if (s <= 2) return { lit: Math.max(s, 1), bar: 'bg-red-500', text: 'text-red-600', label: 'Lemah' };
                if (s <= 4) return { lit: 3, bar: 'bg-amber-500', text: 'text-amber-600', label: 'Sedang' };
                if (s === 5) return { lit: 4, bar: 'bg-green-500', text: 'text-green-600', label: 'Kuat' };
                return { lit: 4, bar: 'bg-green-600', text: 'text-green-700', label: 'Sangat Kuat' };
            },
            barClass(i) {
                return this.strength && this.strength.lit >= i ? this.strength.bar : 'bg-gray-200';
            },
            textClass() {
                return this.strength ? this.strength.text : '';
            },
            label() {
                return this.strength ? this.strength.label : '';
            },
        }));
    });
</script>
@endpush
