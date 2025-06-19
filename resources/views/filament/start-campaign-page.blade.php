<x-filament-panels::page>
    <x-filament::section icon="heroicon-o-user-group">
        <x-slot name="heading">
            Select Receivable Groups
        </x-slot>

        <x-slot name="headerEnd">
            {{ $createGroupCommand }}
        </x-slot>

        @if (count($allReceivableGroups))
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($allReceivableGroups as $group)
                    <x-filament::card @class([
                        'cursor-pointer transition hover:ring-2 hover:ring-primary-500/50',
                        'ring-2 ring-primary-500' => in_array($group['FQN'], $selectedGroups),
                    ])>
                        <label class="flex items-start justify-between w-full h-full">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                                    {{ $group['classname'] }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $group['description'] }}
                                </p>
                                <p
                                    class="flex items-center gap-1 mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <x-heroicon-o-document-duplicate class="w-4 h-4" />
                                    {{ $group['total'] }} Receivables
                                </p>
                            </div>

                            <x-filament::input.checkbox wire:key="group-{{ $group['FQN'] }}" value="{{ $group['FQN'] }}"
                                wire:model.live="selectedGroups" class="sr-only" />

                            @if (in_array($group['FQN'], $selectedGroups))
                                <x-heroicon-o-check-circle class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                            @else
                                <div class="w-6 h-6 border-2 border-gray-300 rounded-full dark:border-gray-600"></div>
                            @endif
                        </label>
                    </x-filament::card>
                @endforeach
            </div>
        @else
            <div
                class="flex flex-col items-center justify-center p-8 text-center border-2 border-gray-300 border-dashed rounded-lg dark:border-gray-600">
                <div class="flex items-center justify-center w-12 h-12 mb-4 bg-gray-100 rounded-full dark:bg-gray-800">
                    <x-heroicon-o-x-mark class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    No Receivable Groups Found
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Please create a new receivable group to begin the process.
                </p>
            </div>
        @endif


        <div class="my-4">
            <x-filament::input.wrapper>
                <label for="" class="px-2 pt-1 text-gray-600">Delay Between Jobs</label>
                <x-filament::input type="number" wire:model.defer="delayBetweenJobs"
                    placeholder="Delay between jobs (seconds)" min="0" />
            </x-filament::input.wrapper>
        </div>

        <div class="flex justify-end pt-6 mt-6 border-t border-gray-200 dark:border-white/10">
            <div
                @if ($totalReceivablesWithoutOverlapping <= 0) x-data
                        x-tooltip.raw.interactive.placement.top="'You must select at least one group with receivables to start.'" @endif>
                <x-filament::button color="primary" wire:click="startProcess" :disabled="$totalReceivablesWithoutOverlapping <= 0">
                    Send to ~{{ $totalReceivablesWithoutOverlapping }}
                </x-filament::button>
            </div>

        </div>

    </x-filament::section>
</x-filament-panels::page>
