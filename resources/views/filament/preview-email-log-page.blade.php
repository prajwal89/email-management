<x-filament-panels::page >
    <iframe srcdoc="{{ $record->html }}" style="height: 500px;"
        sandbox="allow-same-origin allow-scripts" class="w-full max-w-3xl border rounded shadow bg-gray-100/40">
    </iframe>
</x-filament-panels::page>
