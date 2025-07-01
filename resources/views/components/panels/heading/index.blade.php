@props([
    'heading',
    'subheading' => null,
    'action' => null,
])

<div class="relative mb-6 w-full">
    <div class="flex">
        <dvi class="grow">
            <flux:heading size="xl" level="1">{{ $heading }}</flux:heading>
            @isset($heading)
                <flux:subheading size="lg" class="mb-6">{{ $subheading }}</flux:subheading>
            @endisset
        </dvi>
        @isset($action)
            <div class="flex-none">
                {{ $action }}
            </div>
        @endisset
    </div>
    <flux:separator variant="subtle" />
</div>
