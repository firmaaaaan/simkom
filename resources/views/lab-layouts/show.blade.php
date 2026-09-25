@extends('layouts.app')

@section('title', 'Preview: {{ $labLayout->name }}')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <a href="{{ route('lab-layouts.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors mb-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $labLayout->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                Laboratorium: <strong>{{ $labLayout->laboratory->name }}</strong>
                | Grid: {{ $labLayout->grid_cols }} x {{ $labLayout->grid_rows }} ({{ $labLayout->cell_size }}px)
                | Objek: {{ $labLayout->items->count() }}
                | Status: <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $labLayout->is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $labLayout->is_published ? 'Published' : 'Draft' }}</span>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('lab-layouts.edit', $labLayout) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                </svg>
                Edit Layout
            </a>
            @if(!$labLayout->is_published)
                <form action="{{ route('lab-layouts.publish', $labLayout) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors"
                            onclick="return confirm('Publikasikan layout ini? Layout lain di laboratorium yang sama akan dinonaktifkan.')">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        Publikasikan
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Canvas Preview --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center gap-4 text-sm text-gray-600">
                <span>Canvas: {{ $labLayout->canvas_width }} x {{ $labLayout->canvas_height }} px</span>
                <span>Cell: {{ $labLayout->cell_size }}px</span>
                <span class="ml-auto">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="toggleGrid" checked onchange="toggleGrid(this.checked)" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        Tampilkan Grid
                    </label>
                </span>
            </div>
        </div>

        <div class="p-4 relative" style="background: {{ $labLayout->background_color }};">
            <svg id="previewSvg"
                 :viewBox="'0 0 ' + canvasWidth + ' ' + canvasHeight"
                 class="w-full h-auto border border-gray-200 rounded-lg"
                 style="max-width: 100%; height: auto;"
                 x-data="{
                     canvasWidth: {{ $labLayout->canvas_width }},
                     canvasHeight: {{ $labLayout->canvas_height }},
                     cellSize: {{ $labLayout->cell_size }},
                     gridCols: {{ $labLayout->grid_cols }},
                     gridRows: {{ $labLayout->grid_rows }},
                     items: @json($labLayout->items),
                     showGrid: true,
                     itemW(item) { return item.type === 'computer' ? 80 : 30; },
                     itemH(item) { return item.type === 'computer' ? 60 : 80; },
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
                     }
                 }">
                <defs>
                    <pattern id="gridPattern" :width="cellSize" :height="cellSize" patternUnits="userSpaceOnUse">
                        <path d="M {{ $labLayout->cell_size }} 0 L 0 0 0 {{ $labLayout->cell_size }}" fill="none" stroke="#e5e7eb" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" :fill="showGrid ? 'url(#gridPattern)' : 'none'" />
                
                <template x-for="item in items" :key="item.id">
                    <g :transform="itemTransform(item)">
                        <rect :width="itemW(item)" :height="itemH(item)"
                              :class="item.type === 'computer' ? 'fill-blue-100 stroke-blue-500' : 'fill-gray-200 stroke-gray-500'"
                              rx="4" stroke-width="2"/>
                        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
                              class="text-xs font-medium pointer-events-none"
                              :transform="textTransform(item)">
                            @{{ item.label }}
                        </text>
                    </g>
                </template>
            </svg>
        </div>
    </div>
</div>

<script>
function toggleGrid(checked) {
    const svg = document.getElementById('previewSvg');
    if (svg && svg.__x) {
        svg.__x.showGrid = checked;
    }
}
</script>
@endsection