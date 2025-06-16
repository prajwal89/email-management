<x-filament-panels::page>
    {{-- @if (count($this->record->emailVariants) > 1)
        <div>
            @foreach ($this->record->emailVariants as $emailVariant)
                <button>{{ $emailVariant->name }}</button>,
            @endforeach
        </div>
    @else
        <iframe srcdoc="{{ $emailContent }}" style="height: 500px;" sandbox="allow-same-origin allow-scripts"
            class="w-full max-w-3xl p-2 bg-gray-100 border rounded shadow">
        </iframe>
        @endif --}}
        @foreach ( $record->emailVariants()->with('sendable')->get() as $emailVariant )
        <button>{{ $emailVariant->name }}</button>,
        <iframe srcdoc="{{ $emailVariant->resolveEmailHandler()::buildSampleEmail($emailVariant)->render() }}" style="height: 500px;" sandbox="allow-same-origin allow-scripts"
            class="w-full max-w-3xl p-2 bg-gray-100 border rounded shadow">
        </iframe>


    @endforeach

</x-filament-panels::page>
