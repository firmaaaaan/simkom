@extends('layouts.app')

@section('title', 'Edit Denah: {{ $labLayout->name }}')

@section('content')
<div x-data="layoutEditor()" class="h-screen flex bg-gray-100" x-init="init()">
    {{-- Sidebar Kiri - Objek & Toolbar --}}
    <aside class="w-72 bg-white border-r border-gray-200 flex flex-col overflow-y-auto hidden lg:flex">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Tambah Objek</h3>
            
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-500 mb-1">Komputer</label>
                <select x-ref="computerSelect" @change="addComputer($event.target.value)" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white">
                    <option value="">-- Pilih Komputer --</option>
                    <template x-for="comp in computers" :key="comp.id">
                        <option :value="comp.id">@{{ comp.code }} - @{{ comp.name }}</option>
                    </template>
                </select>
            </div>
            
            <button @click="addDoor()" 
                    class="w-full px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Pintu
            </button>
        </div>

        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Toolbar</h3>
            <div class="space-y-2">
                <div class="flex gap-2">
                    <button @click="undo()" :disabled="historyIndex <= 0"
                            class="flex-1 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Undo
                    </button>
                    <button @click="redo()" :disabled="historyIndex >= history.length - 1"
                            class="flex-1 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                        Redo
                    </button>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                    <input type="checkbox" x-model="snapGrid" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    Snap ke Grid
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                    <input type="checkbox" x-model="showGrid" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    Tampilkan Grid
                </label>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Background</label>
                    <input type="color" x-model="bgColor" @change="updateBgColor()" class="w-full h-8 border border-gray-300 rounded-lg cursor-pointer">
                </div>
            </div>
        </div>

        <div class="p-4 border-b border-gray-200 flex-1 overflow-y-auto">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Layer</h3>
            <ul class="space-y-1">
                <template x-for="item in items" :key="item.id">
                    <li @click="selectItem(item.id)" 
                        :class="selectedId === item.id ? 'bg-green-50 border-l-2 border-green-500' : ''"
                        class="px-2 py-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <span :class="item.type === 'computer' ? 'text-blue-600' : 'text-gray-600'" class="flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <template x-if="item.type === 'computer'">
                                        <path d="M4.5 5.25A2.25 2.25 0 016.75 3h10.5A2.25 2.25 0 0119.5 5.25v13.5A2.25 2.25 0 0117.25 21h-10.5A2.25 2.25 0 014.5 18.75v-13.5zM6 6.75v10.5a1.5 1.5 0 001.5 1.5h9a1.5 1.5 0 001.5-1.5V6.75m-12 0H6m0 0v-.75a1.5 1.5 0 011.5-1.5h9A1.5 1.5 0 0119.5 7.5v.75M6 6.75h.008v.008H6V6.75zM9 6.75h.008v.008H9V6.75zM12 6.75h.008v.008H12V6.75zM15 6.75h.008v.008H15V6.75z"/>
                                    </template>
                                    <template x-if="item.type === 'door'">
                                        <path fill-rule="evenodd" d="M2.25 3.75c0-.621.504-1.125 1.125-1.125h15.75c.621 0 1.125.504 1.125 1.125v16.5c0 .621-.504 1.125-1.125 1.125H3.375c-.621 0-1.125-.504-1.125-1.125V3.75zM3.75 6a.75.75 0 100 1.5.75.75 0 000-1.5zM3.75 19.5a.75.75 0 100 1.5.75.75 0 000-1.5zM20.25 6a.75.75 0 100 1.5.75.75 0 000-1.5zM20.25 19.5a.75.75 0 100 1.5.75.75 0 000-1.5z" clip-rule="evenodd"/>
                                    </template>
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-gray-900 truncate">@{{ item.label }}</span>
                        </div>
                        <button @click.stop="deleteItem(item.id)" 
                                class="text-gray-400 hover:text-red-600 p-1 rounded transition-colors flex-shrink-0"
                                title="Hapus">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </li>
                </template>
                <li x-show="items.length === 0" class="text-center text-gray-400 text-sm py-4">Belum ada objek</li>
            </ul>
        </div>

        <div class="p-4 border-t border-gray-200">
            <div class="flex gap-2">
                <button @click="saveDraft()" 
                        class="flex-1 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Simpan Draft
                </button>
                @if(!$labLayout->is_published)
                <button @click="publish()" 
                        class="flex-1 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors"
                        onclick="return confirm('Publikasikan layout ini? Layout lain di laboratorium yang sama akan dinonaktifkan.')">
                    Publikasikan
                </button>
                @endif
            </div>
        </div>
    </aside>

    {{-- Canvas Tengah --}}
    <main class="flex-1 relative overflow-auto" 
          @wheel.prevent="zoom($event)" 
          @mousedown.middle="startPan($event)"
          @mousemove="pan($event)"
          @mouseup="endPan()"
          @mouseleave="endPan()">
        <svg :viewBox="viewBox" class="w-full h-full" @click="deselect" 
             :style="{ transform: 'translate(' + panX + 'px, ' + panY + 'px) scale(' + zoomLevel + ')', transformOrigin: '0 0' }">
            <defs>
                <pattern id="grid" :width="cellSize" :height="cellSize" patternUnits="userSpaceOnUse">
                    <path :d="'M ' + cellSize + ' 0 L 0 0 0 ' + cellSize" fill="none" stroke="#e5e7eb" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect :width="canvasWidth" :height="canvasHeight" :fill="bgColor" />
            <rect :width="canvasWidth" :height="canvasHeight" fill="url(#grid)" x-show="showGrid" />
            
            {{-- Items --}}
            <template x-for="item in items" :key="item.id">
                <g :transform="itemTransform(item)" 
                   class="cursor-move"
                   @mousedown="startDrag(item, $event)"
                   :class="{ 'ring-2 ring-green-500': selectedId === item.id }">
                    <rect :width="itemW(item)" :height="itemH(item)" 
                          :class="item.type === 'computer' ? 'fill-blue-100 stroke-blue-500' : 'fill-gray-200 stroke-gray-500'"
                          rx="4" stroke-width="2"/>
                    <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" 
                          class="text-xs font-medium pointer-events-none" 
                          :transform="textTransform(item)">
                        @{{ item.label }}
                    </text>
                    <!-- Rotation Handle (computer only) -->
                    <template x-if="item.type === 'computer'">
                        <circle :cx="itemW(item) + 8" :cy="itemH(item) + 8" r="8" 
                                class="fill-green-500 cursor-grab hover:fill-green-600 transition-colors"
                                @mousedown.stop="startRotate(item, $event)"></circle>
                    </template>
                </g>
            </template>
        </svg>
    </main>

    {{-- Sidebar Kanan - Properties --}}
    <aside x-show="selectedId" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform translate-x-full" x-transition:enter-end="opacity-100 transform translate-x-0" class="w-64 bg-white border-l border-gray-200 flex flex-col hidden lg:flex">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Properti</h3>
        </div>
        <div class="p-4 space-y-4 flex-1 overflow-y-auto">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Label</label>
                <input type="text" x-model="selectedItem.label" @input="saveHistory()" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div x-show="selectedItem.type === 'computer'">
                <label class="block text-xs font-medium text-gray-500 mb-1">Rotasi</label>
                <select x-model="selectedItem.rotation" @change="saveHistory()" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="0">0°</option>
                    <option value="90">90°</option>
                    <option value="180">180°</option>
                    <option value="270">270°</option>
                </select>
            </div>
            <div x-show="selectedItem.type === 'computer'">
                <label class="block text-xs font-medium text-gray-500 mb-1">Komputer</label>
                <select x-model="selectedItem.computer_id" @change="saveHistory()" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">-- Tidak terikat --</option>
                    <template x-for="comp in computers" :key="comp.id">
                        <option :value="comp.id">@{{ comp.code }} - @{{ comp.name }}</option>
                    </template>
                </select>
            </div>
            <div class="pt-4 border-t border-gray-200">
                <button @click="deleteItem(selectedId)" 
                        class="w-full px-4 py-2 text-red-600 hover:bg-red-50 text-sm font-medium rounded-lg transition-colors">
                    Hapus Objek
                </button>
            </div>
        </div>
    </aside>
</div>

<script>
function layoutEditor() {
    return {
        items: @json($labLayout->layout_data ?? []),
        computers: @json($computers->map(fn($c) => ['id' => $c->id, 'code' => $c->code, 'name' => $c->name])),
        labLayoutId: '{{ $labLayout->id }}',
        selectedId: null,
        snapGrid: true,
        showGrid: true,
        bgColor: '{{ $labLayout->background_color }}',
        gridCols: {{ $labLayout->grid_cols }},
        gridRows: {{ $labLayout->grid_rows }},
        cellSize: {{ $labLayout->cell_size }},
        history: [],
        historyIndex: -1,
        zoomLevel: 1,
        panX: 0,
        panY: 0,
        isPanning: false,
        panStartX: 0,
        panStartY: 0,

        get canvasWidth() { return this.gridCols * this.cellSize; },
        get canvasHeight() { return this.gridRows * this.cellSize; },
        get viewBox() { return `0 0 ${this.canvasWidth} ${this.canvasHeight}`; },
        get selectedItem() { 
            return this.items.find(i => i.id === this.selectedId) || null; 
        },

        init() {
            this.saveHistory(); // initial state
        },

        itemW(item) { 
            return item.type === 'computer' ? 80 : 30; 
        },
        itemH(item) { 
            return item.type === 'computer' ? 60 : 80; 
        },

        itemTransform(item) { 
            const x = item.grid_x * this.cellSize;
            const y = item.grid_y * this.cellSize;
            const cx = this.itemW(item)/2, cy = this.itemH(item)/2;
            return `translate(${x},${y}) rotate(${item.rotation||0},${cx},${cy})`;
        },
        textTransform(item) {
            const rot = item.rotation || 0;
            if (rot === 0) return '';
            const cx = this.itemW(item)/2, cy = this.itemH(item)/2;
            return `rotate(${-rot},${cx},${cy})`;
        },

        // Find cell at mouse position
        getCellFromEvent(e) {
            const svg = e.target.closest('svg');
            if (!svg) return null;
            const pt = svg.createSVGPoint();
            pt.x = e.clientX;
            pt.y = e.clientY;
            const ctm = svg.getScreenCTM();
            if (!ctm) return null;
            const svgP = pt.matrixTransform(ctm.inverse());
            const gridX = Math.round(svgP.x / this.cellSize);
            const gridY = Math.round(svgP.y / this.cellSize);
            return { x: Math.max(0, Math.min(this.gridCols - 1, gridX)), y: Math.max(0, Math.min(this.gridRows - 1, gridY)) };
        },

        // Check collision
        isCellOccupied(gridX, gridY, excludeId = null) {
            return this.items.some(item => 
                item.id !== excludeId && 
                item.grid_x === gridX && 
                item.grid_y === gridY
            );
        },

        addComputer(compId) {
            if (!compId) return;
            const comp = this.computers.find(c => c.id === compId);
            if (!comp) return;

            // Find first empty cell
            let placed = false;
            for (let y = 0; y < this.gridRows && !placed; y++) {
                for (let x = 0; x < this.gridCols && !placed; x++) {
                    if (!this.isCellOccupied(x, y)) {
                        this.items.push({
                            type: 'computer',
                            id: 'comp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                            grid_x: x,
                            grid_y: y,
                            rotation: 0,
                            computer_id: comp.id,
                            label: comp.code
                        });
                        placed = true;
                    }
                }
            }
            if (!placed) {
                alert('Grid penuh! Tidak bisa menambah komputer.');
            }
            this.$refs.computerSelect.value = '';
            this.saveHistory();
        },

        addDoor() {
            // Find first empty cell on edge (y=0 or y=gridRows-1)
            let placed = false;
            for (let x = 0; x < this.gridCols && !placed; x++) {
                for (const y of [0, this.gridRows - 1]) {
                    if (!this.isCellOccupied(x, y)) {
                        this.items.push({
                            type: 'door',
                            id: 'door_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                            grid_x: x,
                            grid_y: y,
                            rotation: y === 0 ? 0 : 180,
                            label: 'Pintu'
                        });
                        placed = true;
                        break;
                    }
                }
            }
            if (!placed) {
                alert('Tidak ada ruang di tepi untuk pintu!');
            }
            this.saveHistory();
        },

        startDrag(item, e) {
            e.preventDefault();
            this.selectItem(item.id);
            const startCell = this.getCellFromEvent(e);
            if (!startCell) return;
            const startX = startCell.x;
            const startY = startCell.y;
            const origX = item.grid_x;
            const origY = item.grid_y;

            const moveHandler = (e) => {
                const cell = this.getCellFromEvent(e);
                if (!cell) return;
                const newX = Math.max(0, Math.min(this.gridCols - 1, cell.x));
                const newY = Math.max(0, Math.min(this.gridRows - 1, cell.y));
                if (newX !== item.grid_x || newY !== item.grid_y) {
                    if (!this.isCellOccupied(newX, newY, item.id)) {
                        item.grid_x = newX;
                        item.grid_y = newY;
                    }
                }
            };
            const upHandler = () => {
                document.removeEventListener('mousemove', moveHandler);
                document.removeEventListener('mouseup', upHandler);
                if (item.grid_x !== origX || item.grid_y !== origY) {
                    this.saveHistory();
                }
            };
            document.addEventListener('mousemove', moveHandler);
            document.addEventListener('mouseup', upHandler);
        },

        startRotate(item, e) {
            e.preventDefault();
            e.stopPropagation();
            this.selectItem(item.id);
            const angles = [0, 90, 180, 270];
            const currentIdx = angles.indexOf(item.rotation || 0);
            const nextIdx = (currentIdx + 1) % angles.length;
            item.rotation = angles[nextIdx];
            this.saveHistory();
        },

        selectItem(id) {
            this.selectedId = id;
        },

        deselect() {
            this.selectedId = null;
        },

        deleteItem(id) {
            if (confirm('Hapus objek ini?')) {
                this.items = this.items.filter(item => item.id !== id);
                this.selectedId = null;
                this.saveHistory();
            }
        },

        updateBgColor() {
            this.saveHistory();
        },

        saveHistory() {
            // Deep clone
            const state = JSON.parse(JSON.stringify(this.items));
            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(state);
            if (this.history.length > 50) this.history.shift();
            else this.historyIndex++;
        },

        undo() {
            if (this.historyIndex > 0) {
                this.historyIndex--;
                this.items = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
                this.selectedId = null;
            }
        },

        redo() {
            if (this.historyIndex < this.history.length - 1) {
                this.historyIndex++;
                this.items = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
                this.selectedId = null;
            }
        },

        // Zoom
        zoom(e) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            this.zoomLevel = Math.max(0.25, Math.min(3, this.zoomLevel * delta));
        },

        startPan(e) {
            if (e.button === 1) { // middle click
                e.preventDefault();
                this.isPanning = true;
                this.panStartX = e.clientX - this.panX;
                this.panStartY = e.clientY - this.panY;
            }
        },
        pan(e) {
            if (this.isPanning) {
                this.panX = e.clientX - this.panStartX;
                this.panY = e.clientY - this.panStartY;
            }
        },
        endPan() {
            this.isPanning = false;
        },

        // Save to server
        async saveDraft() {
            try {
                const res = await fetch(`{{ route('lab-layouts.update', $labLayout) }}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        layout_data: this.items,
                        background_color: this.bgColor,
                        grid_cols: this.gridCols,
                        grid_rows: this.gridRows,
                        cell_size: this.cellSize
                    })
                });
                if (res.ok) {
                    this.showToast('Draft disimpan');
                } else {
                    this.showToast('Gagal menyimpan', true);
                }
            } catch (err) {
                this.showToast('Error: ' + err.message, true);
            }
        },

        async publish() {
            if (!confirm('Publikasikan layout ini? Layout lain di laboratorium yang sama akan dinonaktifkan.')) return;
            try {
                const res = await fetch(`{{ route('lab-layouts.publish', $labLayout) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    this.showToast('Layout dipublikasikan');
                    setTimeout(() => window.location.href = '{{ route('lab-layouts.index') }}', 1000);
                } else {
                    this.showToast('Gagal mempublikasikan', true);
                }
            } catch (err) {
                this.showToast('Error: ' + err.message, true);
            }
        },

        showToast(msg, isError = false) {
            // Simple toast
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg text-white text-sm z-50 ${isError ? 'bg-red-600' : 'bg-green-600'}`;
            toast.textContent = msg;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    }
}
</script>
@endsection