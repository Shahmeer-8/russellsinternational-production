<x-filament-panels::page>
    <div class="space-y-6">
        @foreach ($groups as $group)
            <x-filament::section>
                <x-slot name="heading">
                    {{ $group['title'] }}
                </x-slot>

                @if (! empty($group['description']))
                    <x-slot name="description">
                        {{ $group['description'] }}
                    </x-slot>
                @endif

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($group['sections'] as $section)
                        <div class="flex min-h-44 flex-col rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $section['title'] }}</h3>
                                    <p class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-400">{{ $section['meta'] }}</p>
                                </div>

                                <x-filament::badge :color="$section['statusColor']">
                                    {{ $section['status'] }}
                                </x-filament::badge>
                            </div>

                            @if (! empty($section['previewImage']))
                                <img
                                    src="{{ $section['previewImage'] }}"
                                    alt=""
                                    class="mb-3 h-28 w-full rounded-lg object-cover ring-1 ring-gray-950/10 dark:ring-white/10"
                                />
                            @endif

                            <div class="mb-4 flex-1 space-y-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                <p>{{ $section['description'] }}</p>

                                @if (! empty($section['previewTitle']) || ! empty($section['previewBody']))
                                    <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                                        @if (! empty($section['previewTitle']))
                                            <p class="font-semibold text-gray-950 dark:text-white">{{ $section['previewTitle'] }}</p>
                                        @endif

                                        @if (! empty($section['previewBody']))
                                            <p class="mt-1 line-clamp-3 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $section['previewBody'] }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <a
                                href="{{ $section['url'] }}"
                                class="inline-flex w-fit items-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-500"
                            >
                                {{ $section['action'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
