{{-- Pemilih permission per-aksi: per modul ada aksi Lihat/Tambah/Ubah/Hapus
     (aksi yang tidak punya route, mis. Tambah Peminjaman, tidak muncul).
     Parameter: permissions (koleksi), selectedIds (array string UUID).
     Alpine component `permissionPicker` didefinisikan di bagian bawah file ini. --}}
@php
    $selectedIds = $selectedIds ?? [];

    $actionWords = [
        'view' => 'Lihat', 'create' => 'Tambah', 'edit' => 'Ubah', 'delete' => 'Hapus',
        'export' => 'Export', 'import' => 'Import', 'print' => 'Cetak',
    ];
    $actionOrder = ['view', 'create', 'edit', 'delete', 'export', 'import', 'print'];

    $categories = [
        'Pengaturan' => ['users', 'roles', 'backups'],
        'Data Master' => ['laboratories', 'hardware', 'software', 'computers', 'components'],
        'Transaksi & Laporan' => ['academic-years', 'lab-schedules', 'maintenance', 'tickets', 'borrowings', 'lab-usages', 'reports'],
    ];

    // Kelompokkan permission per modul: name = <aksi>-<modul>.
    $byModule = [];
    $unparsed = [];
    foreach ($permissions as $permission) {
        if (preg_match('/^(view|create|edit|delete|export|import|print)-(.+)$/', $permission->name, $m)) {
            $byModule[$m[2]][$m[1]] = $permission;
        } else {
            $unparsed[] = $permission;
        }
    }

    $buildModule = function (array $modulePermissions) use ($actionOrder, $actionWords): array {
        $items = [];
        foreach ($actionOrder as $action) {
            if (isset($modulePermissions[$action])) {
                $items[] = ['permission' => $modulePermissions[$action], 'word' => $actionWords[$action]];
            }
        }

        return [
            'label' => preg_replace('/^(Lihat|Tambah|Ubah|Hapus|Export|Import|Cetak)\s+/', '', $items[0]['permission']->label),
            'items' => $items,
        ];
    };

    // Susunan tampil: kategori => modul => [label, items].
    $structure = [];
    $categorized = [];
    foreach ($categories as $category => $modules) {
        foreach ($modules as $module) {
            if (! isset($byModule[$module])) {
                continue;
            }
            $structure[$category][$module] = $buildModule($byModule[$module]);
            $categorized[] = $module;
        }
    }

    // Modul yang tidak masuk kategori mana pun (jaga-jaga).
    foreach (array_diff(array_keys($byModule), $categorized) as $module) {
        $structure['Lainnya'][$module] = $buildModule($byModule[$module]);
    }

    // Permission yang namanya tidak mengikuti pola <aksi>-<modul>.
    if ($unparsed !== []) {
        $structure['Lainnya'] = $structure['Lainnya'] ?? [];
    }

    // Nama permission per kategori & per modul (untuk toggle Alpine).
    $groups = [];
    $modulesAlpine = [];
    foreach ($structure as $category => $categoryModules) {
        foreach ($categoryModules as $key => $module) {
            foreach ($module['items'] as $item) {
                $groups[$category][] = $item['permission']->name;
                $modulesAlpine[$key][] = $item['permission']->name;
            }
        }
    }
    foreach ($unparsed as $permission) {
        $groups['Lainnya'][] = $permission->name;
    }
@endphp

<div x-data="permissionPicker({{ collect($selectedIds)->map(fn ($id) => (string) $id)->toJson() }}, {{ collect($permissions)->map(fn ($p) => ['id' => (string) $p->id, 'name' => $p->name])->toJson() }}, @js($groups), @js($modulesAlpine))">
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
        @foreach($structure as $category => $categoryModules)
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">{{ $category }}</p>
                    <button type="button" @click="toggleGroup(@js($category))"
                        class="text-xs font-medium text-green-600 hover:text-green-700">
                        <span x-text="groupFullySelected(@js($category)) ? 'Kosongkan' : 'Pilih grup'"></span>
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3">
                    @foreach($categoryModules as $key => $module)
                        <div class="border border-gray-100 rounded-lg p-2.5 bg-gray-50/60">
                            <div class="flex items-center justify-between mb-2 gap-2">
                                <p class="text-sm font-semibold text-gray-700 truncate">{{ $module['label'] }}</p>
                                <button type="button" @click="toggleModule(@js($key))"
                                    class="text-xs font-medium text-green-600 hover:text-green-700 shrink-0">
                                    <span x-text="moduleFullySelected(@js($key)) ? 'Kosongkan' : 'Pilih'"></span>
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($module['items'] as $item)
                                    <label class="flex items-center gap-2 px-2 py-1.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-white transition-colors"
                                        title="{{ $item['permission']->label }} ({{ $item['permission']->name }})">
                                        <input type="checkbox" name="permissions[]" value="{{ $item['permission']->id }}"
                                            @checked(in_array($item['permission']->id, $selectedIds, true))
                                            :checked="selected.includes('{{ $item['permission']->id }}')"
                                            @change="toggle('{{ $item['permission']->id }}')"
                                            class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="text-sm text-gray-700">{{ $item['word'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @php $flatUnparsed = collect($unparsed); @endphp
                    @if($category === 'Lainnya' && $flatUnparsed->isNotEmpty())
                        <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($flatUnparsed as $permission)
                                <label class="flex items-start gap-2 px-3 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-white transition-colors">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                        @checked(in_array($permission->id, $selectedIds, true))
                                        :checked="selected.includes('{{ $permission->id }}')"
                                        @change="toggle('{{ $permission->id }}')"
                                        class="mt-0.5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-gray-800">{{ $permission->label }}</span>
                                        <span class="block text-xs text-gray-400">{{ $permission->name }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('permissionPicker', (initialSelected, permissions, groups, modules) => ({
            selected: initialSelected,
            permissions,
            groups,
            modules,
            get idByName() {
                return Object.fromEntries(this.permissions.map(p => [p.name, p.id]));
            },
            idsOf(names) {
                return names.map(name => this.idByName[name]).filter(Boolean);
            },
            idsOfGroup(label) {
                return this.idsOf(this.groups[label] || []);
            },
            idsOfModule(key) {
                return this.idsOf(this.modules[key] || []);
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
            toggleModule(key) {
                const ids = this.idsOfModule(key);
                this.selected = this.moduleFullySelected(key)
                    ? this.selected.filter(id => !ids.includes(id))
                    : [...new Set([...this.selected, ...ids])];
            },
            moduleFullySelected(key) {
                const ids = this.idsOfModule(key);
                return ids.length > 0 && ids.every(id => this.selected.includes(id));
            },
        }));
    });
</script>
@endpush
