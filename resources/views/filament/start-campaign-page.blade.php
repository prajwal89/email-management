<x-filament-panels::page>
    <x-filament::section icon="heroicon-o-user">
        <x-slot name="heading">
            Select Groups
        </x-slot>
        <x-slot name="headerEnd">
            {{ $createGroupCommand }}
        </x-slot>
        <div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach ($allReceivableGroups as $group)
                    <x-filament::card>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $group['classname'] }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $group['description'] }}</p>
                            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $group['total'] }} Receivables
                            </p>
                        </div>


                        <label class="flex items-center space-x-2">
                            <x-filament::input.checkbox wire:key="group-{{ $group['FQN'] }}" value="{{ $group['FQN'] }}"
                                wire:model.live="selectedGroups" />
                            <span>Select</span>
                        </label>
                    </x-filament::card>
                @endforeach
            </div>
            <div class="flex justify-end mt-6">
                <x-filament::button color="primary" wire:click="startProcess" :disabled="$totalReceivablesWithoutOverlapping <= 0 || !is_null($record->started_on)">
                    Start ({{ $totalReceivablesWithoutOverlapping }})
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
