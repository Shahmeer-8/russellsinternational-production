<x-filament::section>
    <x-slot name="heading">
        Website Content Map
    </x-slot>

    <x-slot name="description">
        Use this guide to find the exact admin area for each visible website section.
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($groups as $group)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $group['title'] }}</h3>
                    <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $group['description'] }}</p>
                </div>

                <div class="space-y-3">
                    @foreach ($group['items'] as $item)
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-950 dark:text-white">{{ $item['label'] }}</div>
                                    <div class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $item['hint'] }}</div>
                                </div>

                                @if ($item['url'])
                                    <a
                                        href="{{ $item['url'] }}"
                                        class="shrink-0 rounded-md bg-primary-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-primary-500"
                                    >
                                        Edit
                                    </a>
                                @else
                                    <span class="shrink-0 rounded-md bg-gray-200 px-2.5 py-1.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        Hidden
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament::section>
