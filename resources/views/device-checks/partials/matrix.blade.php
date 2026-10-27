@php
    $header = \App\Models\DeviceCheck::headerGroups();
    $columns = \App\Models\DeviceCheck::itemColumns();
    $statuses = $statuses ?? [];
    $editable = $editable ?? false;
    $statusLabels = \App\Models\DeviceCheckItem::statuses();
    $statusSymbols = \App\Models\DeviceCheckItem::symbols();
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="bg-gray-50">
                <th rowspan="2" class="px-3 py-2 font-medium text-gray-600 border border-gray-200 w-10">No</th>
                <th rowspan="2" class="px-3 py-2 text-left font-medium text-gray-600 border border-gray-200 min-w-[110px]">ID PC</th>
                @foreach($header as $column)
                    @if($column['type'] === 'single')
                        <th rowspan="2" class="px-2 py-2 font-medium text-gray-600 border border-gray-200 w-20">{{ $column['label'] }}</th>
                    @else
                        <th colspan="{{ count($column['columns']) }}" class="px-2 py-2 font-medium text-gray-600 border border-gray-200">{{ $column['label'] }}</th>
                    @endif
                @endforeach
            </tr>
            <tr class="bg-gray-50">
                @foreach($header as $column)
                    @if($column['type'] === 'group')
                        @foreach($column['columns'] as $sub)
                            <th class="px-2 py-2 font-medium text-gray-600 border border-gray-200 w-16 text-xs">{{ $sub['label'] }}</th>
                        @endforeach
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($computers as $index => $computer)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-3 py-2 text-center text-gray-500 border border-gray-200">{{ $index + 1 }}</td>
                    <td class="px-3 py-2 font-medium text-gray-900 border border-gray-200">{{ $computer->code }}</td>
                    @foreach($columns as $column)
                        @php
                            $key = $computer->id . '.' . $column['key'];
                            $selected = $statuses[$key] ?? \App\Models\DeviceCheckItem::STATUS_BERFUNGSI;
                        @endphp
                        <td class="px-1 py-1.5 text-center border border-gray-200">
                            @if($editable)
                                <div class="inline-flex rounded-md border border-gray-200 overflow-hidden bg-white" role="radiogroup"
                                    aria-label="{{ $computer->code }} - {{ $column['label'] }}">
                                    @foreach($statusLabels as $statusKey => $statusLabel)
                                        <label class="cursor-pointer border-r border-gray-200 last:border-r-0" title="{{ $statusLabel }}">
                                            <input type="radio" class="status-radio sr-only peer" name="items[{{ $column['key'] }}][{{ $computer->id }}]" value="{{ $statusKey }}" {{ $selected === $statusKey ? 'checked' : '' }}>
                                            <span class="flex items-center justify-center w-6 h-6 text-xs font-bold text-gray-400 bg-white transition-colors peer-checked:text-white
                                                {{ $statusKey === \App\Models\DeviceCheckItem::STATUS_BERFUNGSI ? 'peer-checked:bg-green-600' : ($statusKey === \App\Models\DeviceCheckItem::STATUS_RUSAK ? 'peer-checked:bg-red-600' : 'peer-checked:bg-gray-500') }}">
                                                {{ $statusSymbols[$statusKey] }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                @php $status = $statuses[$key] ?? null; @endphp
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded border-2 text-xs font-bold
                                    {{ $status === \App\Models\DeviceCheckItem::STATUS_BERFUNGSI ? 'border-green-500 bg-green-500 text-white' : ($status === \App\Models\DeviceCheckItem::STATUS_RUSAK ? 'border-red-400 bg-red-50 text-red-600' : ($status === \App\Models\DeviceCheckItem::STATUS_TIDAK_ADA ? 'border-gray-300 bg-white text-gray-400' : 'border-gray-200 bg-white text-gray-300')) }}"
                                    title="{{ $statusLabels[$status] ?? 'Belum dicek' }}">
                                    {{ $statusSymbols[$status] ?? '' }}
                                </span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 2 + \App\Models\DeviceCheck::itemColumnCount() }}" class="px-4 py-12 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm">Belum ada komputer di laboratorium ini</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
