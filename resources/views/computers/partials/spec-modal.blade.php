{{-- Modal Spesifikasi Komputer (hanya hardware).
     Membutuhkan `specOpen` (boolean) pada x-data terdekat. --}}
<div x-show="specOpen" x-cloak x-transition.opacity.duration.150ms
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    @click="specOpen = false"
    @keydown.escape.window="specOpen = false">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" @click.stop>
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div>
                <h3 class="font-bold text-gray-800">Spesifikasi Komputer</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $computer->code }}</p>
            </div>
            <button type="button" @click="specOpen = false" class="text-gray-400 hover:text-gray-600" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-auto p-6">
            <p class="text-sm font-semibold text-gray-800 mb-2">
                Hardware <span class="font-normal text-gray-400">({{ $computer->hardware->count() }})</span>
            </p>
            @if($computer->hardware->isNotEmpty())
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 font-medium">Nama</th>
                                <th class="px-3 py-2 font-medium">Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($computer->hardware as $hw)
                                <tr>
                                    <td class="px-3 py-2 text-gray-800">{{ $hw->name }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $hw->category }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-400 italic">Belum ada hardware</p>
            @endif
        </div>
    </div>
</div>
