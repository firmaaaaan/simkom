{{-- Input password dengan toggle ikon mata.
    Parameter: name (wajib), id (opsional, default = name), autocomplete & placeholder (opsional). --}}
<div x-data="{ show: false }" class="relative">
    <input type="password" :type="show ? 'text' : 'password'" name="{{ $name }}" id="{{ $id ?? $name }}"
        @isset($autocomplete) autocomplete="{{ $autocomplete }}" @endisset
        class="w-full px-4 py-2.5 pr-11 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
        placeholder="{{ $placeholder ?? 'Masukkan password' }}">
    <button type="button" @click="show = !show" tabindex="-1"
        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition-colors"
        :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
        @include('profile.partials.eye-icons')
    </button>
</div>
