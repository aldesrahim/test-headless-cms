<th {{ $attributes->merge(['class' => 'w-1']) }}>
    @if (! empty($slot))
        {{ $slot }}
    @else
        <span class="sr-only">Actions</span>
    @endif
</th>
