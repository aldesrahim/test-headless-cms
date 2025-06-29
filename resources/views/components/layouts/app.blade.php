<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        <x-flash-alert />

        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
