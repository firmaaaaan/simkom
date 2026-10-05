{{-- Modal Spesifikasi Komputer.
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

        <div class="flex-1 overflow-auto p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Laboratorium</p>
                    <p class="text-sm font-medium text-gray-800">{{ $computer->laboratory?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Status</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $computer->status === 'Aktif' ? 'bg-green-100 text-green-800' : ($computer->status === 'Maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                        {{ $computer->status }}
                    </span>
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-500 mb-1">Keterangan</p>
                <p class="text-sm text-gray-800">{{ $computer->description ?: '-' }}</p>
            </div>

            <div>
                <p class="text-sm font-semibold text-gray-800 mb-2">
                    Hardware <span class="font-normal text-gray-400">({{ $computer->hardware->count() }})</span>
                </p>
                @if($computer->hardware->isNotEmpty())
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-3 py-2 font-medium">Kode</th>
                                    <th class="px-3 py-2 font-medium">Nama</th>
                                    <th class="px-3 py-2 font-medium">Brand</th>
                                    <th class="px-3 py-2 font-medium">Model</th>
                                    <th class="px-3 py-2 font-medium">Kategori</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($computer->hardware as $hw)
                                    <tr>
                                        <td class="px-3 py-2 font-mono text-xs text-gray-600">{{ $hw->code }}</td>
                                        <td class="px-3 py-2 text-gray-800">{{ $hw->name }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $hw->brand ?? '-' }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $hw->model ?? '-' }}</td>
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

            <div>
                <p class="text-sm font-semibold text-gray-800 mb-2">
                    Software <span class="font-normal text-gray-400">({{ $computer->software->count() }})</span>
                </p>
                @if($computer->software->isNotEmpty())
                    <ul class="space-y-2">
                        @foreach($computer->software as $sw)
                            <li class="flex items-center justify-between gap-3 px-3 py-2 border border-gray-200 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $sw->name }}</p>
                                    <p class="text-xs text-gray-500">{{ collect([$sw->version, $sw->category])->filter()->implode(' • ') }}</p>
                                </div>
                                <span class="shrink-0 px-2 py-0.5 rounded-full text-xs font-medium {{ $sw->status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $sw->status }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-400 italic">Belum ada software</p>
                @endif
            </div>
        </div>
    </div>
</div>
