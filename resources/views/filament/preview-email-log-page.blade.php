<x-filament-panels::page>

    {{-- Main two-column layout --}}
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-5">

        {{-- LEFT/MAIN Column: Email Content Viewer with Tabs --}}
        {{-- Alpine.js is used here to manage the active tab state --}}
        <div class="lg:col-span-3" x-data="{ activeTab: 'html' }" x-cloak>
            <div class="mb-4">
                <x-filament::tabs>
                    <x-filament::tabs.item x-bind:active="activeTab === 'html'" @click="activeTab = 'html'"
                        icon="heroicon-o-code-bracket">
                        HTML
                    </x-filament::tabs.item>

                    <x-filament::tabs.item x-bind:active="activeTab === 'html-content'"
                        @click="activeTab = 'html-content'" icon="heroicon-o-code-bracket">
                        HTML Content
                    </x-filament::tabs.item>

                    <x-filament::tabs.item x-bind:active="activeTab === 'text'" @click="activeTab = 'text'"
                        icon="heroicon-o-bars-3-bottom-left">
                        Text
                    </x-filament::tabs.item>

                    <x-filament::tabs.item x-bind:active="activeTab === 'headers'" @click="activeTab = 'headers'"
                        icon="heroicon-o-book-open">
                        Headers
                    </x-filament::tabs.item>
                </x-filament::tabs>
            </div>

            {{-- Tab Content --}}
            <div>
                {{-- HTML Content --}}
                <div x-show="activeTab === 'html'"
                    class="bg-white border shadow-sm rounded-xl dark:bg-gray-900/50 dark:border-white/10">
                    <iframe srcdoc="{{ $record->html }}" style="height: 75vh; max-height: 800px;"
                        sandbox="allow-same-origin" class="w-full h-full border-0 rounded-xl"></iframe>
                </div>

                <div x-show="activeTab === 'html-content'" style="display: none;">
                    <x-filament::section>
                        <x-slot name="heading" class="sr-only">Html Content</x-slot>
                        @if ($record->html)
                            <pre class="font-mono text-sm text-gray-700 whitespace-pre-wrap dark:text-gray-300">{{ $record->html }}</pre>
                        @else
                            <div class="flex flex-col items-center justify-center h-48 gap-3 text-gray-500">
                                <x-heroicon-o-document-minus class="w-12 h-12" />
                                <span class="text-lg">No Html version available.</span>
                            </div>
                        @endif
                    </x-filament::section>
                </div>

                {{-- Text Content --}}
                <div x-show="activeTab === 'text'" style="display: none;">
                    <x-filament::section>
                        <x-slot name="heading" class="sr-only">Plain Text</x-slot>
                        @if ($record->text)
                            <pre class="font-mono text-sm text-gray-700 whitespace-pre-wrap dark:text-gray-300">{{ $record->text }}</pre>
                        @else
                            <div class="flex flex-col items-center justify-center h-48 gap-3 text-gray-500">
                                <x-heroicon-o-document-minus class="w-12 h-12" />
                                <span class="text-lg">No plain text version available.</span>
                            </div>
                        @endif
                    </x-filament::section>
                </div>

                {{-- Headers Content --}}
                <div x-show="activeTab === 'headers'" style="display: none;">
                    <x-filament::section>
                        <x-slot name="heading" class="sr-only">Headers</x-slot>
                        <dl class="grid grid-cols-1 gap-y-4 sm:grid-cols-8">
                            @forelse($record->headers ?? [] as $header => $value)
                                <dt
                                    class="text-sm font-medium text-center text-gray-500 sm:col-span-1 dark:text-gray-400">
                                    {{ $header }}</dt>
                                <dd class="font-mono text-sm text-gray-900 break-all sm:col-span-7 dark:text-gray-100">
                                    {{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                            @empty
                                <p class="text-gray-500">No headers were recorded.</p>
                            @endforelse
                        </dl>
                    </x-filament::section>
                </div>
            </div>
        </div>

        {{-- RIGHT Column: Details Side Panel --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Status & Summary Section --}}
            <x-filament::section>
                <x-slot name="heading">
                    Summary
                </x-slot>

                @php
                    $status = $record->getStatus();
                    $statusDetails = [
                        'sent' => ['color' => 'primary', 'icon' => 'heroicon-s-paper-airplane'],
                        'opened' => ['color' => 'success', 'icon' => 'heroicon-s-eye'],
                        'clicked' => ['color' => 'success', 'icon' => 'heroicon-s-cursor-arrow-rays'],
                        'replied' => ['color' => 'success', 'icon' => 'heroicon-s-chat-bubble-left-right'],
                        'unsubscribed' => ['color' => 'warning', 'icon' => 'heroicon-s-user-minus'],
                        'complained' => ['color' => 'warning', 'icon' => 'heroicon-s-shield-exclamation'],
                        'soft_bounced' => ['color' => 'danger', 'icon' => 'heroicon-s-exclamation-triangle'],
                        'hard_bounced' => ['color' => 'danger', 'icon' => 'heroicon-s-x-circle'],
                        'pending' => ['color' => 'gray', 'icon' => 'heroicon-s-clock'],
                    ];
                    $currentStatus = $statusDetails[$status] ?? $statusDetails['pending'];
                @endphp

                <div class="flex items-center justify-between gap-4">
                    <span class="text-lg font-medium text-gray-900 dark:text-white">Status</span>
                    <x-filament::badge :color="$currentStatus['color']" :icon="$currentStatus['icon']">
                        {{ \Illuminate\Support\Str::of($status)->replace('_', ' ')->title() }}
                    </x-filament::badge>
                </div>

                <dl class="grid grid-cols-2 gap-4 mt-4">
                    <div class="p-4 text-center bg-gray-100 rounded-lg dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Opens</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-200">
                            {{ $record->opens ?? 0 }}</dd>
                    </div>
                    <div class="p-4 text-center bg-gray-100 rounded-lg dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Clicks</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-200">
                            {{ $record->clicks ?? 0 }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- Details Section --}}
            <x-filament::section>
                <x-slot name="heading">
                    Details
                </x-slot>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subject</dt>
                        <dd class="mt-1 text-base text-gray-900 dark:text-gray-200">{{ $record->subject }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">From</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900 dark:text-gray-200">{{ $record->from }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Recipients</dt>
                        <dd class="mt-1 space-y-2 text-sm text-gray-900 dark:text-gray-200">
                            @forelse($record->recipients->groupBy('type') as $type => $recipients)
                                <div>
                                    <span
                                        class="font-semibold text-gray-600 capitalize dark:text-gray-300">{{ $type }}:</span>
                                    <ul class="pl-2">
                                        @foreach ($recipients as $recipient)
                                            <li class="font-mono">{{ $recipient->email }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @empty
                                <p class="text-gray-500">No recipients recorded.</p>
                            @endforelse
                        </dd>
                    </div>

                    {{-- Helper function to generate a link to a Filament resource page --}}
                    @php
                        if (!function_exists('getFilamentResourceLink')) {
                            function getFilamentResourceLink($modelClass, $modelId)
                            {
                                if (!$modelClass || !$modelId) {
                                    return null;
                                }
                                try {
                                    $resource = \Filament\Facades\Filament::getResourceForModel($modelClass);
                                    if (!$resource) {
                                        return null;
                                    }
                                    return $resource::getUrl('view', ['record' => $modelId]);
                                } catch (\Exception $e) {
                                    return null;
                                }
                            }
                        }
                    @endphp

                    {{-- @if ($record->receivable)
                         <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Recipient Model</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                @if ($link = getFilamentResourceLink($record->receivable_type, $record->receivable_id))
                                    <a href="{{ $link }}" class="text-primary-600 hover:underline dark:text-primary-400">
                                        {{ \Illuminate\Support\Str::afterLast($record->receivable_type, '\\') }} #{{ $record->receivable_id }}
                                    </a>
                                @else
                                    {{ \Illuminate\Support\Str::afterLast($record->receivable_type, '\\') }} #{{ $record->receivable_id }}
                                @endif
                            </dd>
                        </div>
                    @endif --}}
                    {{-- @if ($record->sendable)
                         <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Source Model</dt>
                             <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                @if ($link = getFilamentResourceLink($record->sendable_type, $record->sendable_id))
                                    <a href="{{ $link }}" class="text-primary-600 hover:underline dark:text-primary-400">
                                        {{ \Illuminate\Support\Str::afterLast($record->sendable_type, '\\') }} #{{ $record->sendable_id }}
                                    </a>
                                @else
                                    {{ \Illuminate\Support\Str::afterLast($record->sendable_type, '\\') }} #{{ $record->sendable_id }}
                                @endif
                            </dd>
                        </div>
                    @endif --}}
                </dl>
            </x-filament::section>

            {{-- Timeline Section --}}
            <x-filament::section>
                <x-slot name="heading">
                    Event Timeline
                </x-slot>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @php
                            $event_details = [
                                'sent_at' => [
                                    'label' => 'Sent',
                                    'icon' => 'heroicon-m-paper-airplane',
                                    'color' => 'text-blue-500',
                                ],
                                'last_opened_at' => [
                                    'label' => 'Last Opened',
                                    'icon' => 'heroicon-m-eye',
                                    'color' => 'text-green-500',
                                ],
                                'last_clicked_at' => [
                                    'label' => 'Last Clicked',
                                    'icon' => 'heroicon-m-cursor-arrow-rays',
                                    'color' => 'text-green-500',
                                ],
                                'replied_at' => [
                                    'label' => 'Replied',
                                    'icon' => 'heroicon-m-chat-bubble-left-right',
                                    'color' => 'text-green-500',
                                ],
                                'complained_at' => [
                                    'label' => 'Complaint',
                                    'icon' => 'heroicon-m-shield-exclamation',
                                    'color' => 'text-yellow-500',
                                ],
                                'unsubscribed_at' => [
                                    'label' => 'Unsubscribed',
                                    'icon' => 'heroicon-m-user-minus',
                                    'color' => 'text-yellow-500',
                                ],
                                'soft_bounced_at' => [
                                    'label' => 'Soft Bounced',
                                    'icon' => 'heroicon-m-exclamation-triangle',
                                    'color' => 'text-red-500',
                                ],
                                'hard_bounced_at' => [
                                    'label' => 'Hard Bounced',
                                    'icon' => 'heroicon-m-x-circle',
                                    'color' => 'text-red-500',
                                ],
                            ];
                            
                            // Helper function to ensure Carbon instance
                            $ensureCarbon = function($date) {
                                if (!$date) return null;
                                if ($date instanceof \Carbon\Carbon) return $date;
                                try {
                                    return \Carbon\Carbon::parse($date);
                                } catch (\Exception $e) {
                                    return null;
                                }
                            };
                            
                            $events = collect($event_details)
                                ->map(function($details, $field) use ($record, $ensureCarbon) {
                                    $date = $ensureCarbon($record->{$field});
                                    return $date ? ['date' => $date, 'details' => $details] : null;
                                })
                                ->filter()
                                ->sortByDesc('date');
                        @endphp
                        @forelse($events as $event)
                            <li>
                                <div class="relative pb-8">
                                    @if (!$loop->last)
                                        <span
                                            class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
                                            aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                class="flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full dark:bg-gray-800 ring-8 ring-white dark:ring-gray-900">
                                                <x-dynamic-component :component="$event['details']['icon']" :class="'w-5 h-5 ' . $event['details']['color']" />
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0 pt-1.5">
                                            <p class="text-sm text-gray-500">
                                                {{ $event['details']['label'] }}
                                                <span class="font-medium text-gray-900 dark:text-gray-200"
                                                    title="{{ $event['date']->toIso8601String() }}">
                                                    {{ $event['date']->diffForHumans() }}
                                                </span>
                                            </p>
                                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                                {{ $event['date']->format('M d, Y, H:i:s T') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li>
                                <div class="relative flex items-center space-x-3">
                                    <div>
                                        <span
                                            class="flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full dark:bg-gray-800">
                                            <x-heroicon-m-clock class="w-5 h-5 text-gray-400" />
                                        </span>
                                    </div>
                                    <div class="pt-1.5">
                                        <p class="text-sm text-gray-500">Email is pending and has not been sent yet.</p>
                                    </div>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </x-filament::section>

            {{-- Technical Details (Collapsible) --}}
            <x-filament::section :collapsible="true" :collapsed="true">
                <x-slot name="heading">
                    Technical Details
                </x-slot>
                <dl class="space-y-4">
                    <div x-data="{
                        copied: false,
                        textToCopy: '{{ $record->message_id }}',
                        copy() {
                            if (!this.textToCopy) return;
                            navigator.clipboard.writeText(this.textToCopy);
                            this.copied = true;
                            setTimeout(() => this.copied = false, 2000);
                        }
                    }">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Message ID</dt>
                        <dd class="flex items-center gap-2 mt-1">
                            <span
                                class="flex-1 font-mono text-sm text-gray-900 break-all dark:text-gray-200">{{ $record->message_id ?: 'N/A' }}</span>
                            <button x-show="textToCopy" @click="copy()" type="button"
                                class="p-1 text-gray-400 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <x-heroicon-o-clipboard-document class="w-4 h-4" x-show="!copied" />
                                <x-heroicon-s-check class="w-4 h-4 text-green-500" x-show="copied"
                                    style="display: none;" />
                                <span class="sr-only">Copy Message ID</span>
                            </button>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mailer / Transport</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ $record->mailer }} /
                            {{ $record->transport }}</dd>
                    </div>
                    @if ($record->context)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Context Data</dt>
                            <dd class="mt-1">
                                <pre
                                    class="p-3 font-mono text-xs text-gray-800 whitespace-pre-wrap bg-gray-100 rounded-lg dark:bg-gray-800 dark:text-gray-300">{{ json_encode($record->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>