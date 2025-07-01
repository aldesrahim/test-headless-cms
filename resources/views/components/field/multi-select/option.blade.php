@props([
    'value',
    'label' => null,
])

<div
    class="group/option group flex w-full items-center overflow-hidden rounded-md px-2 py-1.5 text-start text-sm font-medium text-zinc-800 hover:bg-zinc-100 focus:outline-hidden data-hidden:hidden dark:text-white dark:hover:bg-zinc-600 [&[disabled]]:text-zinc-400 dark:[&[disabled]]:text-zinc-400"
    role="option"
    data-value="{{ $value }}"
    @click="toggleSelection($el)"
>
    <div class="w-6 shrink-0 [ui-selected_&]:hidden">
        <flux:icon class="hidden size-5 shrink-0 [div[data-selected]_&]:block" name="check" />
    </div>

    {{ $label ?? $value }}
</div>
