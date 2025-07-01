@props([
    'paginator' => null,
])

<div
    class="divide-y divide-gray-200 overflow-hidden rounded-xl shadow-md ring-1 ring-gray-950/5 dark:divide-white/10 dark:ring-white/10"
>
    <div class="divide-y divide-gray-200 dark:divide-white/10">
        <div class="flex items-center justify-between gap-x-4 px-4 py-3 sm:px-6">
            {{ $toolbar ?? null }}
        </div>
    </div>
    <div class="relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
        <table class="w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
            @if (! empty($columns))
                <thead class="divide-y divide-gray-200 dark:divide-white/5">
                    <tr class="bg-gray-50 dark:bg-white/5">
                        {{ $columns }}
                    </tr>
                </thead>
            @endif

            @if (! empty($rows))
                <tbody class="divide-y divide-zinc-800/10 dark:divide-white/20 [&:not(:has(*))]:border-t-0!">
                    {{ $rows }}
                </tbody>
            @endif
        </table>
    </div>

    {{ $pagination ?? null }}
</div>
