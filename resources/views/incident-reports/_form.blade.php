@php
    $isEdit = isset($incidentReport);
    $existingImages = $isEdit ? ($incidentReport->images ?? []) : [];
@endphp

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
    {{-- Hari, Tanggal & Waktu --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Waktu Kejadian</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hari, Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_kejadian" value="{{ old('tanggal_kejadian', $isEdit ? $incidentReport->tanggal_kejadian?->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <p class="text-xs text-gray-500 mt-1">Nama hari ditampilkan otomatis mengikuti tanggal.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Waktu <span class="text-red-500">*</span></label>
                <input type="time" name="waktu_kejadian" value="{{ old('waktu_kejadian', $isEdit ? $incidentReport->waktu : '') }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
        </div>

        <div class="mt-5">
            <label class="block text-sm font-medium text-gray-700 mb-2">Telah terjadi <span class="text-red-500">*</span></label>
            @php
                $selectedJenis = old('telah_terjadi', $isEdit ? $incidentReport->telah_terjadi : 'Kecelakaan');
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ jenis: @js($selectedJenis) }">
                @foreach(\App\Models\IncidentReport::JENIS_KEJADIAN as $value => $label)
                    <label class="flex items-center gap-3 px-4 py-3 border rounded-lg cursor-pointer transition-colors"
                        :class="jenis === @js($value) ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:bg-gray-50'">
                        <input type="radio" name="telah_terjadi" value="{{ $value }}" x-model="jenis" @checked($selectedJenis === $value) required class="text-green-600 focus:ring-green-500">
                        <span class="text-sm" :class="jenis === @js($value) ? 'font-medium text-green-800' : 'text-gray-700'">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Lokasi & Korban --}}
    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Lokasi &amp; Korban</h3>
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Kejadian <span class="text-red-500">*</span></label>
                <input type="text" name="lokasi" value="{{ old('lokasi', $isEdit ? $incidentReport->lokasi : '') }}" required placeholder="Contoh: Lab Komputer 1" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kecelakaan atas Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_korban" value="{{ old('nama_korban', $isEdit ? $incidentReport->nama_korban : '') }}" required placeholder="Nama korban" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan', $isEdit ? $incidentReport->jabatan : '') }}" placeholder="Contoh: Mahasiswa / Laboran" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Asal Institusi/Fakultas/Prodi/Unit</label>
                <input type="text" name="asal_institusi" value="{{ old('asal_institusi', $isEdit ? $incidentReport->asal_institusi : '') }}" placeholder="Contoh: Fakultas Teknik / Prodi Informatika" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
        </div>
    </div>

    {{-- Uraian Kejadian --}}
    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Uraian Kejadian</h3>
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sebab-sebab Kecelakaan</label>
                <textarea name="sebab" rows="3" placeholder="Penyebab terjadinya kecelakaan..." class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('sebab', $isEdit ? $incidentReport->sebab : '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Riwayat Kejadian</label>
                <textarea name="riwayat" rows="4" placeholder="Kronologi kejadian dari awal sampai akhir..." class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('riwayat', $isEdit ? $incidentReport->riwayat : '') }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Saksi</label>
                    <textarea name="saksi" rows="3" placeholder="Nama saksi (pisahkan dengan koma bila lebih dari satu)..." class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('saksi', $isEdit ? $incidentReport->saksi : '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Akibat Kejadian</label>
                    <textarea name="akibat" rows="3" placeholder="Dampak/kerugian yang timbul..." class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('akibat', $isEdit ? $incidentReport->akibat : '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Penilaian Risiko --}}
    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-1">Penilaian Risiko (Risk Rating)</h3>
        <p class="text-xs text-gray-500 mb-3">Kemungkinan (Likelihood) &times; Dampak (Impact) = Risk Score. Level risiko dihitung otomatis.</p>

        @php
            $selectedLikelihood = old('likelihood', $isEdit ? $incidentReport->likelihood : '');
            $selectedImpact = old('impact', $isEdit ? $incidentReport->impact : '');
        @endphp

        <div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kemungkinan (Likelihood) <span class="text-red-500">*</span></label>
                    <select id="risk-likelihood" name="likelihood" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Kemungkinan</option>
                        @foreach(\App\Models\IncidentReport::LIKELIHOODS as $score => $label)
                            <option value="{{ $score }}" @selected((string) $selectedLikelihood === (string) $score)>{{ $label }} ({{ $score }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dampak (Impact) <span class="text-red-500">*</span></label>
                    <select id="risk-impact" name="impact" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Dampak</option>
                        @foreach(\App\Models\IncidentReport::IMPACTS as $score => $label)
                            <option value="{{ $score }}" @selected((string) $selectedImpact === (string) $score)>{{ $label }} ({{ $score }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row sm:items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                <div class="flex items-baseline gap-2">
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Risk Score</span>
                    <span id="risk-score" class="text-2xl font-bold text-gray-900">-</span>
                </div>
                <div class="sm:ml-auto">
                    <span id="risk-level" class="hidden inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"></span>
                    <span id="risk-level-hint" class="text-xs text-gray-500">Pilih Kemungkinan &amp; Dampak untuk melihat level risiko</span>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px]">
                <span class="text-gray-500">Risk Rating Key:</span>
                <span class="px-2 py-0.5 rounded-full font-medium bg-green-100 text-green-700">Low 1&ndash;4</span>
                <span class="px-2 py-0.5 rounded-full font-medium bg-yellow-100 text-yellow-700">Moderate 5&ndash;9</span>
                <span class="px-2 py-0.5 rounded-full font-medium bg-orange-100 text-orange-700">High 10&ndash;15</span>
                <span class="px-2 py-0.5 rounded-full font-medium bg-red-100 text-red-700">Very High 16&ndash;25</span>
            </div>
        </div>

        {{-- JavaScript murni: tetap jalan di form internal maupun form publik (tanpa Alpine). --}}
        <script>
            (function () {
                const likelihood = document.getElementById('risk-likelihood');
                const impact = document.getElementById('risk-impact');
                const scoreEl = document.getElementById('risk-score');
                const levelEl = document.getElementById('risk-level');
                const hintEl = document.getElementById('risk-level-hint');
                if (!likelihood || !impact || !scoreEl || !levelEl || !hintEl) return;

                const colors = {
                    'Low': 'bg-green-100 text-green-700',
                    'Moderate': 'bg-yellow-100 text-yellow-700',
                    'High': 'bg-orange-100 text-orange-700',
                    'Very High': 'bg-red-100 text-red-700',
                };

                function levelFor(score) {
                    if (score <= 4) return 'Low';
                    if (score <= 9) return 'Moderate';
                    if (score <= 15) return 'High';
                    return 'Very High';
                }

                function render() {
                    const l = Number(likelihood.value);
                    const i = Number(impact.value);

                    if (!l || !i) {
                        scoreEl.textContent = '-';
                        levelEl.className = 'hidden inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold';
                        levelEl.textContent = '';
                        hintEl.classList.remove('hidden');
                        return;
                    }

                    const score = l * i;
                    const level = levelFor(score);

                    scoreEl.textContent = score;
                    levelEl.textContent = level;
                    levelEl.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ' + (colors[level] || 'bg-gray-100 text-gray-600');
                    hintEl.classList.add('hidden');
                }

                likelihood.addEventListener('change', render);
                impact.addEventListener('change', render);
                render();
            })();
        </script>
    </div>

    {{-- Pelapor & Bukti --}}
    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Pelaporan</h3>
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dilaporkan Oleh <span class="text-red-500">*</span></label>
                <input type="text" name="pelapor" value="{{ old('pelapor', $isEdit ? $incidentReport->pelapor : ($defaultPelapor ?? auth()->user()?->name)) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bukti Dokumentasi Kejadian</label>
                <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="w-full text-sm text-gray-600 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                <p class="text-xs text-gray-500 mt-1">Maksimal {{ $isEdit ? 5 - count($existingImages) : 5 }} file gambar (JPG/PNG/WEBP, maks. 5 MB per file).</p>
            </div>

            @if($isEdit && count($existingImages))
                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">Bukti tersimpan</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($existingImages as $image)
                            <label class="relative block rounded-lg overflow-hidden border border-gray-200 group cursor-pointer">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" alt="Bukti dokumentasi" class="w-full h-24 object-cover">
                                <span class="absolute bottom-0 inset-x-0 bg-white/90 px-2 py-1 flex items-center gap-1.5 text-[11px] text-gray-700">
                                    <input type="checkbox" name="remove_images[]" value="{{ $image }}" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    Hapus
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
