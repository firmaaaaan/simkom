{{-- Pemilih permission: dikelompokkan per kategori (mengikuti struktur menu sidebar).
    Parameter: permissions (koleksi), selectedIds (array int).
    Alpine component `permissionPicker` didefinisikan di bagian bawah file ini. --}}
@php
    $selectedIds = $selectedIds ?? [];

    $groups = [
        'Pengaturan' => ['manage-users', 'manage-roles'],
        'Data Master' => ['manage-laboratories', 'manage-hardware', 'manage-software', 'manage-computers', 'manage-components'],
        'Transaksi & Laporan' => ['manage-academic-years', 'manage-maintenance', 'view-reports', 'manage-tickets', 'manage-borrowings'],
    ];
@endphp

<div x-data="permissionPicker({{ collect($selectedIds)->map(fn ($id) => (int) $id)->toJson() }}, {{ collect($permissions)->map(fn ($p) => ['id' => (int) $p->id, 'name' => $p->name])->toJson() }}, @js($groups)) }}">
    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
        <p class="text-xs text-gray-500">
            Terpilih: <span class="font-semibold text-gray-700" x-text="selected.length"></span> dari {{ $permissions->count() }} permission
        </p>
        <div class="flex items-center gap-2">
            <button type="button" @click="selectAll()" class="text-xs font-medium text-green-600 hover:text-green-700">Pilih semua</button>
            <span class="text-gray-300">|</span>
            <button type="button" @click="selected = []" class="text-xs font-medium text-gray-500 hover:text-gray-700">Hapus semua</button>
        </div>
    </div>

    <div class="space-y-4">
        @foreach($groups as $groupLabel => $groupNames)
            @php $groupPermissions = $permissions->whereIn('name', $groupNames)->values(); @endphp

            @if($groupPermissions->isNotEmpty())
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                        <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">{{ $groupLabel }}</p>
                        <button type="button" @click="toggleGroup(@js($groupLabel))"
                            class="text-xs font-medium text-green-600 hover:text-green-700">
                            <span x-text="groupFullySelected(@js($groupLabel)) ? 'Kosongkan' : 'Pilih grup'"></span>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3">
                        @foreach($groupPermissions as $permission)
                            <label class="flex items-start gap-2 px-3 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                    @checked(in_array($permission->id, $selectedIds))
                                    :checked="selected.includes({{ (int) $permission->id }})"
                                    @change="toggle({{ (int) $permission->id }})"
                                    class="mt-0.5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-gray-800">{{ $permission->label }}</span>
                                    <span class="block text-xs text-gray-400">{{ $permission->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('permissionPicker', (initialSelected, permissions, groups) => ({
            selected: initialSelected,
            permissions,
            groups,
            get idByName() {
                return Object.fromEntries(this.permissions.map(p => [p.name, p.id]));
            },
            idsOfGroup(label) {
                return (this.groups[label] || [])
                    .map(name => this.idByName[name])
                    .filter(Boolean);
            },
            toggle(id) {
                this.selected = this.selected.includes(id)
                    ? this.selected.filter(v => v !== id)
                    : [...this.selected, id];
            },
            selectAll() {
                this.selected = this.permissions.map(p => p.id);
            },
            toggleGroup(label) {
                const ids = this.idsOfGroup(label);
                this.selected = this.groupFullySelected(label)
                    ? this.selected.filter(id => !ids.includes(id))
                    : [...new Set([...this.selected, ...ids])];
            },
            groupFullySelected(label) {
                const ids = this.idsOfGroup(label);
                return ids.length > 0 && ids.every(id => this.selected.includes(id));
            },
        }));
    });
</script>
@endpush
