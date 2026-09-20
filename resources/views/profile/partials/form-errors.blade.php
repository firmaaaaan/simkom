{{-- Error validasi untuk satu error bag. Parameter: bag (nama error bag). --}}
@php $messages = isset($errors) ? $errors->getBag($bag) : null; @endphp
@if($messages?->any())
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($messages->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
