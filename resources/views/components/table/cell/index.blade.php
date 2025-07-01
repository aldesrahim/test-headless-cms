<td
    {{ $attributes->merge(['class' => 'p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3']) }}
>
    <div class="grid w-full gap-y-1 px-3 py-4">
        <div class="flex">
            <div class="flex max-w-max">
                <div class="inline-flex items-center gap-1.5">
                    <span class="text-sm leading-6 text-zinc-800 dark:text-zinc-300">
                        {{ $slot }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</td>
