<x-filament-panels::page class="w-full">
    <div class="grid w-full">
        <div class="">
            <div class="bg-white border rounded-lg shadow-sm">
                <!-- Tab Navigation -->
                <div class="border-b">
                    <nav class="flex px-4 space-x-8" aria-label="Tabs">
                        <button onclick="switchTab('html')" id="html-tab" class="px-1 py-4 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 whitespace-nowrap tab-button active">
                            HTML
                        </button>
                        @if($record->text)
                            <button onclick="switchTab('text')" id="text-tab" class="px-1 py-4 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 whitespace-nowrap tab-button">
                                Text
                            </button>
                        @endif
                        @if($record->headers && count($record->headers) > 0)
                            <button onclick="switchTab('headers')" id="headers-tab" class="px-1 py-4 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 whitespace-nowrap tab-button">
                                Headers
                            </button>
                        @endif
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-4">
                    <!-- HTML Tab -->
                    <div id="html-content" class="tab-content">
                        <iframe srcdoc="{{ $record->html }}" 
                                style="height: 600px;"
                                sandbox="allow-same-origin allow-scripts" 
                                class="w-full border rounded shadow bg-gray-100/40">
                        </iframe>
                    </div>

                    <!-- Text Tab -->
                    @if($record->text)
                        <div id="text-content" class="hidden tab-content">
                            <div class="p-4 border rounded bg-gray-50" style="height: 600px; overflow-y: auto;">
                                <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ $record->text }}</pre>
                            </div>
                        </div>
                    @endif

                    <!-- Headers Tab -->
                    @if($record->headers && count($record->headers) > 0)
                        <div id="headers-content" class="hidden tab-content">
                            <div class="p-4 border rounded bg-gray-50" style="height: 600px; overflow-y: auto;">
                                <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ json_encode($record->headers, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="">
            <!-- Email Status Card -->
            <div class="bg-white border rounded-lg shadow-sm">
                <div class="p-4 border-b">
                    <h4 class="font-semibold text-gray-900">Email Status</h4>
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-600">Current Status</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            @switch($record->getStatus())
                                @case('replied')
                                    bg-green-100 text-green-800
                                    @break
                                @case('clicked')
                                    bg-blue-100 text-blue-800
                                    @break
                                @case('opened')
                                    bg-yellow-100 text-yellow-800
                                    @break
                                @case('sent')
                                    bg-gray-100 text-gray-800
                                    @break
                                @case('hard_bounced')
                                    bg-red-100 text-red-800
                                    @break
                                @case('soft_bounced')
                                    bg-orange-100 text-orange-800
                                    @break
                                @case('complained')
                                    bg-purple-100 text-purple-800
                                    @break
                                @case('unsubscribed')
                                    bg-pink-100 text-pink-800
                                    @break
                                @default
                                    bg-gray-100 text-gray-800
                            @endswitch
                        ">
                            {{ ucfirst(str_replace('_', ' ', $record->getStatus())) }}
                        </span>
                    </div>
                    
                    <!-- Engagement Metrics -->
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Opens</span>
                            <span class="font-medium">{{ $record->opens ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Clicks</span>
                            <span class="font-medium">{{ $record->clicks ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Details Card -->
            <div class="bg-white border rounded-lg shadow-sm">
                <div class="p-4 border-b">
                    <h4 class="font-semibold text-gray-900">Email Details</h4>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <label class="text-xs font-medium tracking-wide text-gray-500 uppercase">Message ID</label>
                        <p class="font-mono text-sm text-gray-900">{{ $record->message_id }}</p>
                    </div>
                    
                    <div>
                        <label class="text-xs font-medium tracking-wide text-gray-500 uppercase">Subject</label>
                        <p class="text-sm text-gray-900">{{ $record->subject }}</p>
                    </div>
                    
                    <div>
                        <label class="text-xs font-medium tracking-wide text-gray-500 uppercase">From</label>
                        <p class="text-sm text-gray-900">{{ $record->from }}</p>
                    </div>
                    
                    <div>
                        <label class="text-xs font-medium tracking-wide text-gray-500 uppercase">Mailer</label>
                        <p class="text-sm text-gray-900">{{ $record->mailer }}</p>
                    </div>
                    
                    <div>
                        <label class="text-xs font-medium tracking-wide text-gray-500 uppercase">Transport</label>
                        <p class="text-sm text-gray-900">{{ $record->transport }}</p>
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="bg-white border rounded-lg shadow-sm">
                <div class="p-4 border-b">
                    <h4 class="font-semibold text-gray-900">Timeline</h4>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        @if($record->sent_at)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 mt-2 bg-blue-500 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Sent</p>
                                    <p class="text-xs text-gray-500">{{ $record->sent_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($record->last_opened_at)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 mt-2 bg-yellow-500 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Last Opened</p>
                                    <p class="text-xs text-gray-500">{{ $record->last_opened_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($record->last_clicked_at)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 mt-2 bg-green-500 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Last Clicked</p>
                                    <p class="text-xs text-gray-500">{{ $record->last_clicked_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($record->replied_at)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 mt-2 bg-green-600 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Replied</p>
                                    <p class="text-xs text-gray-500">{{ $record->replied_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($record->complained_at)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 mt-2 bg-purple-500 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Complained</p>
                                    <p class="text-xs text-gray-500">{{ $record->complained_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($record->soft_bounced_at)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 mt-2 bg-orange-500 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Soft Bounced</p>
                                    <p class="text-xs text-gray-500">{{ $record->soft_bounced_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($record->hard_bounced_at)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 mt-2 bg-red-500 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Hard Bounced</p>
                                    <p class="text-xs text-gray-500">{{ $record->hard_bounced_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($record->unsubscribed_at)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 mt-2 bg-pink-500 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Unsubscribed</p>
                                    <p class="text-xs text-gray-500">{{ $record->unsubscribed_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if(!$record->sent_at && !$record->last_opened_at && !$record->last_clicked_at && !$record->replied_at && !$record->complained_at && !$record->soft_bounced_at && !$record->hard_bounced_at && !$record->unsubscribed_at)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 mt-2 bg-gray-400 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Pending</p>
                                    <p class="text-xs text-gray-500">Email is queued for sending</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recipients Card -->
            @if($record->recipients()->count() > 0)
                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="p-4 border-b">
                        <h4 class="font-semibold text-gray-900">Recipients ({{ $record->recipients()->count() }})</h4>
                    </div>
                    <div class="p-4">
                        <div class="space-y-2 overflow-y-auto max-h-40">
                            @foreach($record->recipients()->limit(5)->get() as $recipient)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-900">{{ $recipient->email }}</span>
                                    <span class="text-xs text-gray-500">{{ ucfirst($recipient->status ?? 'pending') }}</span>
                                </div>
                            @endforeach
                            @if($record->recipients()->count() > 5)
                                <div class="pt-2 text-xs text-center text-gray-500">
                                    And {{ $record->recipients()->count() - 5 }} more...
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Email Visits Card -->
            @if($record->emailVisits()->count() > 0)
                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="p-4 border-b">
                        <h4 class="font-semibold text-gray-900">Recent Visits ({{ $record->emailVisits()->count() }})</h4>
                    </div>
                    <div class="p-4">
                        <div class="space-y-2 overflow-y-auto max-h-40">
                            @foreach($record->emailVisits()->latest()->limit(5)->get() as $visit)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-900">{{ $visit->ip_address ?? 'Unknown IP' }}</span>
                                    <span class="text-xs text-gray-500">{{ $visit->created_at->format('M d, h:i A') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Context Data Card -->
            @if($record->context && count($record->context) > 0)
                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="p-4 border-b">
                        <h4 class="font-semibold text-gray-900">Context Data</h4>
                    </div>
                    <div class="p-4">
                        <div class="space-y-2">
                            @foreach($record->context as $key => $value)
                                <div>
                                    <label class="text-xs font-medium tracking-wide text-gray-500 uppercase">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
                                    <p class="text-sm text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions Card -->
            {{-- <div class="bg-white border rounded-lg shadow-sm">
                <div class="p-4 border-b">
                    <h4 class="font-semibold text-gray-900">Actions</h4>
                </div>
                <div class="p-4 space-y-2">
                    <button type="button" 
                            onclick="window.open('{{ route('filament.admin.resources.email-logs.view', $record) }}', '_blank')" 
                            class="w-full px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Open in New Tab
                    </button>
                </div>
            </div> --}}
        </div>
    </div>
</x-filament-panels::page>