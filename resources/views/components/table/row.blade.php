<tr
    {{ $attributes->merge(['class' => '[@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5']) }}
>
    {{ $slot }}
</tr>
