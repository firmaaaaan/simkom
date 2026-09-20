@php
    $header = \App\Models\DeviceCheck::headerGroups();
    $columns = \App\Models\DeviceCheck::itemColumns();
    $editable = $editable ?? false;
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
                        @php $key = $computer->id . '.' . $column['key']; @endphp
                        <td class="px-2 py-2 text-center border border-gray-200">
                            @if($editable)
                                <input type="checkbox" name="items[{{ $column['key'] }}][{{ $computer->id }}]" value="1"
                                    title="{{ $computer->code }} - {{ $column['label'] }}"
                                    class="check-item rounded border-gray-300 text-green-600 focus:ring-green-500"
                                    {{ !empty($checked[$key]) ? 'checked' : '' }}>
                            @elseif(!empty($checked[$key]))
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded border-2 border-green-500 bg-green-500">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded border-2 border-gray-200 bg-white"></span>
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
