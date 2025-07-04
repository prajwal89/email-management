<x-filament-panels::page>
    @php
        $emailVariants = $record->emailVariants()->with('sendable')->get();
    @endphp

    <div x-data="{ activeTab: '{{ $emailVariants->first()?->slug }}' }">
        <!-- Tab buttons -->
        <div class="flex mb-4 space-x-2 border-b border-gray-300">
            @foreach ($emailVariants as $emailVariant)
                <button
                    @click="activeTab = '{{ $emailVariant->slug }}'"
                    :class="activeTab === '{{ $emailVariant->slug }}' ? 'border-b-2 border-blue-500 font-bold' : 'text-gray-500'"
                    class="px-4 py-2 focus:outline-none"
                >
                    {{ $emailVariant->name }}
                </button>
            @endforeach
        </div>

        <!-- Tab content (iframe previews) -->
        @foreach ($emailVariants as $emailVariant)
            <div x-show="activeTab === '{{ $emailVariant->slug }}'" x-cloak>
                <iframe srcdoc="{{ $emailVariant->resolveEmailHandler()::buildSampleEmail($emailVariant)->render() }}"
                    style="height: 500px;"
                    sandbox="allow-same-origin allow-scripts"
                    class="w-full max-w-3xl p-2 bg-gray-100 border rounded shadow">
                </iframe>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
