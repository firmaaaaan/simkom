@if($items->isEmpty())
    <p class="text-sm text-gray-400">Belum ada data</p>
@else
    <table class="w-full text-sm">
        <tbody>
            @foreach($items as $item)
                <tr class="border-b border-gray-50 last:border-0">
                    <td class="py-1.5 pr-2 text-gray-700 {{ str_starts_with($item['label'], 'Tidak') ? 'italic text-gray-400' : '' }}">
                        {{ $item['label'] }}
                    </td>
                    <td class="py-1.5 text-right font-semibold text-gray-800 whitespace-nowrap">
                        {{ $item['count'] }}
                        @if($total > 0 && ! str_starts_with($item['label'], 'Tidak'))
                            <span class="text-xs font-normal text-gray-400">({{ round($item['count'] / $total * 100) }}%)</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
