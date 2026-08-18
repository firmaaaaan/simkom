@props(['name', 'label', 'group', 'items', 'errorKey'])

@php
    $id = 'ss-' . \Illuminate\Support\Str::random(8);
@endphp

<div class="select-searchable position-relative mb-3" data-group="{{ $group }}" data-name="{{ $name }}">
    <label class="form-label">{{ $label }}</label>

    <select name="{{ $name }}[]" multiple class="d-none ss-native" id="{{ $id }}-native">
        @foreach ($items as $it)
            <option value="{{ $it['id'] }}" {{ $it['selected'] ? 'selected' : '' }}>{{ $it['label'] }}</option>
        @endforeach
    </select>

    <div class="ss-tags d-flex flex-wrap gap-2 mb-1"></div>

    <div class="input-group">
        <input type="text" class="form-control ss-input" placeholder="Ketik untuk memfilter..." autocomplete="off" />
        <span class="input-group-text"><i class="bx bx-chevron-down"></i></span>
    </div>

    <div class="ss-list dropdown-menu w-100" style="position: absolute; top: 100%; left: 0; z-index: 1100; display: none; max-height: 240px; overflow-y: auto;">
        @forelse ($items as $it)
            <div class="ss-option py-1" data-value="{{ $it['id'] }}" style="cursor: pointer;">
                <div class="form-check m-0 px-2">
                    <input class="form-check-input ss-checkbox" type="checkbox"
                           id="{{ $id }}-cb-{{ $it['id'] }}"
                           value="{{ $it['id'] }}"
                           {{ $it['selected'] ? 'checked' : '' }} />
                    <label class="form-check-label mb-0 ms-1" for="{{ $id }}-cb-{{ $it['id'] }}">{{ $it['label'] }}</label>
                </div>
            </div>
        @empty
            <div class="px-2 py-2 small text-muted">Tidak ada data</div>
        @endforelse
    </div>

    @error($errorKey)
        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
    @enderror
</div>
