<th {{ $attributes->merge(['class' => 'px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6']) }}>
    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
        <span class="text-sm font-semibold text-zinc-800 dark:text-white">
            {{ $slot }}
        </span>
    </span>
</th>
