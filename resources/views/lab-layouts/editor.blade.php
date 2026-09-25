@extends('layouts.app')

@section('title', 'Edit Denah: {{ $labLayout->name }}')

@section('content')
<div x-data="layoutEditor()" class="min-h-screen bg-gray-100" x-init="init()">
    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <a href="{{ route('lab-layouts.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">{{ $labLayout->name }}</h1>
                        <p class="text-sm text-gray-500">{{ $labLayout->laboratory->name }} • Grid {{ $labLayout->grid_cols }}x{{ $labLayout->grid_rows }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $labLayout->is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $labLayout->is_published ? 'Published' : 'Draft' }}
                    </span>
                    <button @click="saveDraft()" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Simpan Draft
                    </button>
                    @if(!$labLayout->is_published)
                    <button @click="publish()" class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                        Publikasikan
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Sidebar Kiri: Available Computers & Tools --}}
            <aside class="lg:col-span-3 space-y-6">
                
                {{-- Available Computers (not placed) --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">Komputer Tersedia</h3>
                        <span class="text-sm text-gray-500" x-text="availableComputers.length + ' unit'"></span>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <template x-for="comp in availableComputers" :key="comp.id">
                            <div class="computer-card p-3 bg-gray-50 border border-gray-200 rounded-lg cursor-grab hover:border-green-300 hover:bg-green-50 transition-colors flex items-center gap-3"
                                 draggable="true"
                                 @dragstart="dragStart($event, comp)"
                                 @dragend="dragEnd($event)">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate" x-text="comp.code"></p>
                                    <p class="text-xs text-gray-500 truncate" x-text="comp.name || 'Komputer'"></p>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                </svg>
                            </div>
                        </template>
                        <div x-show="availableComputers.length === 0" class="text-center text-gray-400 text-sm py-4">
                            Semua komputer sudah ditempatkan
                        </div>
                    </div>
                </div>

                {{-- Tools: Door, Grid Settings --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Alat</h3>
                    
                    <button @click="addDoor()" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition-colors flex items-center justify-center gap-2 text-gray-700 font-medium">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Pintu
                    </button>

                    <div class="border-t border-gray-200 pt-4 space-y-3">
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" x-model="showGrid" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            Tampilkan Grid
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" x-model="snapGrid" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            Snap ke Grid
                        </label>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Background</label>
                            <input type="color" x-model="bgColor" @change="saveHistory()" class="w-full h-8 border border-gray-300 rounded-lg cursor-pointer">
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 flex gap-2">
                        <button @click="undo()" :disabled="historyIndex <= 0" class="flex-1 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50 transition-colors flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                            Undo
                        </button>
                        <button @click="redo()" :disabled="historyIndex >= history.length - 1" class="flex-1 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50 transition-colors flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            Redo
                        </button>
                    </div>
                </div>
            </aside>

            {{-- Grid Canvas Area --}}
            <section class="lg:col-span-9">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    {{-- Grid Toolbar --}}
                    <div class="p-4 border-b border-gray-200 flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <span>Zoom:</span>
                            <button @click="zoomOut()" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-gray-600">-</button>
                            <span x-text="Math.round(zoomLevel * 100) + '%'"></span>
                            <button @click="zoomIn()" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-gray-600">+</button>
                            <button @click="resetZoom()" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-gray-600">Reset</button>
                        </div>
                        <div class="flex-1"></div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Cols:</label>
                            <input type="number" x-model.number="gridCols" @change="updateGrid()" min="4" max="50" class="w-16 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-green-500">
                            <label class="text-sm text-gray-600">Rows:</label>
                            <input type="number" x-model.number="gridRows" @change="updateGrid()" min="3" max="50" class="w-16 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>

                    {{-- Grid Canvas --}}
                    <div class="p-4 overflow-auto" style="max-height: 70vh;">
                        <div class="inline-block" :style="{ transform: 'scale(' + zoomLevel + ')', transformOrigin: 'top left' }">
                            <div class="grid gap-1" :style="{ gridTemplateColumns: 'repeat(' + gridCols + ', ' + cellSize + 'px)' }">
                                <template x-for="y in gridRows" :key="y">
                                    <template x-for="x in gridCols" :key="x">
                                        <div class="grid-cell relative bg-gray-50 border-2 border-dashed border-gray-200 rounded-lg transition-colors"
                                             :style="{ width: cellSize + 'px', height: cellSize + 'px' }"
                                             :class="{ 
                                                'border-green-400 bg-green-50': isDropTarget(x-1, y-1),
                                                'border-red-400 bg-red-50': isOccupied(x-1, y-1) && !isDropTarget(x-1, y-1)
                                             }"
                                             @dragover.prevent="dragOver($event, x-1, y-1)"
                                             @dragleave="dragLeave($event, x-1, y-1)"
                                             @drop="drop($event, x-1, y-1)"
                                             @click="cellClick(x-1, y-1)">
                                            
                                            <template x-if="getItemAt(x-1, y-1)">
                                                <div class="placed-item absolute inset-0 m-1 p-2 rounded-lg shadow-sm cursor-move flex flex-col items-center justify-center"
                                                     :style="{ 
                                                        backgroundColor: getItemAt(x-1, y-1).type === 'computer' ? '#dbeafe' : '#f3f4f6',
                                                        borderColor: getItemAt(x-1, y-1).type === 'computer' ? '#3b82f6' : '#9ca3af',
                                                        transform: 'rotate(' + (getItemAt(x-1, y-1).rotation || 0) + 'deg)'
                                                     }"
                                                     draggable="true"
                                                     @dragstart="dragStartItem($event, getItemAt(x-1, y-1))"
                                                     @dragend="dragEndItem($event)"
                                                     @click.stop="selectItem(getItemAt(x-1, y-1).id)"
                                                     :class="{ 'ring-2 ring-green-500': selectedId === getItemAt(x-1, y-1).id }">
                                                    
                                                    <div class="flex items-center justify-center mb-1">
                                                        <template x-if="getItemAt(x-1, y-1).type === 'computer'">
                                                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                            </svg>
                                                        </template>
                                                        <template x-if="getItemAt(x-1, y-1).type === 'door'">
                                                            <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                                                            </svg>
                                                        </template>
                                                    </div>
                                                    <p class="text-xs font-medium text-center truncate w-full px-1" x-text="getItemAt(x-1, y-1).label"></p>
                                                    
                                                    <!-- Rotate handle -->
                                                    <button @click.stop="rotateItem(getItemAt(x-1, y-1).id)" 
                                                            class="absolute -bottom-2 -right-2 w-5 h-5 bg-green-500 text-white rounded-full text-xs flex items-center justify-center hover:bg-green-600 transition-colors"
                                                            title="Rotasi 90°">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.19 7.19a6 6 0 118.488 8.488M19 12H7m8-5l-4 4m0 0l4 4m-4-4v10" />
                                                        </svg>
                                                    </button>
                                                    
                                                    <!-- Delete handle -->
                                                    <button @click.stop="deleteItem(getItemAt(x-1, y-1).id)" 
                                                            class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center hover:bg-red-600 transition-colors"
                                                            title="Hapus">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                            
                                            <div x-show="!getItemAt(x-1, y-1)" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                <span class="text-xs text-gray-300" x-text="(x) + ',' + (y)"></span>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Properties Panel (Bottom/Modal) --}}
        <div x-show="selectedItem" x-transition class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50 lg:relative lg:shadow-none lg:border-0 lg:fixed lg:bottom-auto lg:top-auto lg:left-auto lg:right-auto lg:z-auto lg:max-w-md lg:rounded-xl lg:border lg:m-6">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Properti: <span x-text="selectedItem.label"></span></h3>
                <button @click="deselect()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-4 space-y-4 max-h-64 overflow-y-auto">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Label</label>
                    <input type="text" x-model="selectedItem.label" @input="saveHistory()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <template x-if="selectedItem.type === 'computer'">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Rotasi</label>
                        <select x-model.number="selectedItem.rotation" @change="saveHistory()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="0">0°</option>
                            <option value="90">90°</option>
                            <option value="180">180°</option>
                            <option value="270">270°</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Komputer Terikat</label>
                        <select x-model="selectedItem.computer_id" @change="saveHistory()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">-- Tidak terikat --</option>
                            <template x-for="comp in allComputers" :key="comp.id">
                                <option :value="comp.id" x-text="comp.code"></option>
                            </template>
                        </select>
                    </div>
                </template>
                <div class="pt-4 border-t border-gray-200">
                    <button @click="deleteItem(selectedItem.id)" class="w-full px-4 py-2 text-red-600 hover:bg-red-50 text-sm font-medium rounded-lg transition-colors">
                        Hapus Objek
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function layoutEditor() {
    return {
        // Data
        items: @json($labLayout->layout_data ?? []),
        allComputers: @json($computers->map(fn($c) => ['id' => $c->id, 'code' => $c->code, 'name' => $c->code])),
        labLayoutId: '{{ $labLayout->id }}',
        
        // Grid settings
        gridCols: {{ $labLayout->grid_cols }},
        gridRows: {{ $labLayout->grid_rows }},
        cellSize: {{ $labLayout->cell_size }},
        bgColor: '{{ $labLayout->background_color }}',
        showGrid: true,
        snapGrid: true,
        
        // State
        selectedId: null,
        draggedItem: null,
        draggedFromAvailable: null,
        dropTarget: { x: -1, y: -1 },
        history: [],
        historyIndex: -1,
        zoomLevel: 1,

        init() {
            this.saveHistory();
        },

        // Computed
        get availableComputers() {
            const placedComputerIds = new Set(this.items
                .filter(i => i.type === 'computer' && i.computer_id)
                .map(i => i.computer_id));
            return this.allComputers.filter(c => !placedComputerIds.has(c.id));
        },

        get selectedItem() {
            return this.items.find(i => i.id === this.selectedId) || null;
        },

        // Grid helpers
        getItemAt(x, y) {
            return this.items.find(item => item.grid_x === x && item.grid_y === y) || null;
        },

        isOccupied(x, y) {
            return this.getItemAt(x, y) !== null;
        },

        isDropTarget(x, y) {
            return this.dropTarget.x === x && this.dropTarget.y === y;
        },

        cellClick(x, y) {
            const item = this.getItemAt(x, y);
            if (item) {
                this.selectItem(item.id);
            } else {
                this.deselect();
            }
        },

        selectItem(id) {
            this.selectedId = id;
        },

        deselect() {
            this.selectedId = null;
        },

        // Drag from available computers list
        dragStart(e, comp) {
            this.draggedFromAvailable = comp;
            this.draggedItem = null;
            e.dataTransfer.effectAllowed = 'copy';
        },

        dragEnd(e) {
            this.draggedFromAvailable = null;
            this.draggedItem = null;
            this.dropTarget = { x: -1, y: -1 };
        },

        // Drag from grid item
        dragStartItem(e, item) {
            this.draggedItem = item;
            this.draggedFromAvailable = null;
            e.dataTransfer.effectAllowed = 'move';
            this.selectItem(item.id);
        },

        dragEndItem(e) {
            this.draggedItem = null;
            this.draggedFromAvailable = null;
            this.dropTarget = { x: -1, y: -1 };
        },

        dragOver(e, x, y) {
            e.preventDefault();
            e.dataTransfer.dropEffect = this.draggedFromAvailable ? 'copy' : 'move';
            
            // Check if valid drop target
            if (this.draggedFromAvailable) {
                if (!this.isOccupied(x, y)) {
                    this.dropTarget = { x, y };
                }
            } else if (this.draggedItem) {
                if (!this.isOccupied(x, y) || (this.draggedItem.grid_x === x && this.draggedItem.grid_y === y)) {
                    this.dropTarget = { x, y };
                }
            }
        },

        dragLeave(e, x, y) {
            if (this.dropTarget.x === x && this.dropTarget.y === y) {
                this.dropTarget = { x: -1, y: -1 };
            }
        },

        drop(e, x, y) {
            e.preventDefault();
            
            if (this.draggedFromAvailable) {
                // Add new computer from available list
                if (!this.isOccupied(x, y)) {
                    this.items.push({
                        type: 'computer',
                        id: 'comp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                        grid_x: x,
                        grid_y: y,
                        rotation: 0,
                        computer_id: this.draggedFromAvailable.id,
                        label: this.draggedFromAvailable.code
                    });
                    this.saveHistory();
                }
            } else if (this.draggedItem) {
                // Move existing item
                if (!this.isOccupied(x, y) || (this.draggedItem.grid_x === x && this.draggedItem.grid_y === y)) {
                    const oldX = this.draggedItem.grid_x;
                    const oldY = this.draggedItem.grid_y;
                    this.draggedItem.grid_x = x;
                    this.draggedItem.grid_y = y;
                    if (oldX !== x || oldY !== y) {
                        this.saveHistory();
                    }
                }
            }
            
            this.dropTarget = { x: -1, y: -1 };
            this.draggedItem = null;
            this.draggedFromAvailable = null;
        },

        // Add door
        addDoor() {
            let placed = false;
            for (let x = 0; x < this.gridCols && !placed; x++) {
                for (const y of [0, this.gridRows - 1]) {
                    if (!this.isOccupied(x, y)) {
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

        // Rotate item
        rotateItem(id) {
            const item = this.items.find(i => i.id === id);
            if (item && item.type === 'computer') {
                const angles = [0, 90, 180, 270];
                const currentIdx = angles.indexOf(item.rotation || 0);
                item.rotation = angles[(currentIdx + 1) % angles.length];
                this.saveHistory();
            }
        },

        // Delete item
        deleteItem(id) {
            if (confirm('Hapus objek ini?')) {
                this.items = this.items.filter(item => item.id !== id);
                this.selectedId = null;
                this.saveHistory();
            }
        },

        // Grid settings
        updateGrid() {
            this.saveHistory();
        },

        // Zoom
        zoomIn() {
            this.zoomLevel = Math.min(2, this.zoomLevel + 0.1);
        },
        zoomOut() {
            this.zoomLevel = Math.max(0.5, this.zoomLevel - 0.1);
        },
        resetZoom() {
            this.zoomLevel = 1;
        },

        // History
        saveHistory() {
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