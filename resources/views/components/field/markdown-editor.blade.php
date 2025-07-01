@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'invalid' => null,
    'statePath',
    'toolbarButtons' => [
        'blockquote',
        'bold',
        'bulletList',
        'codeBlock',
        'heading',
        'italic',
        'link',
        'orderedList',
        'redo',
        'strike',
        'table',
        'undo',
    ],
    
])

@php
    $invalid ??= $name && $errors->has($name);

    $classes = Flux::classes()
        ->add('block w-full')
        ->add('shadow-xs disabled:shadow-none border rounded-lg')
        ->add('bg-white dark:bg-white/10 dark:disabled:bg-white/[7%]')
        ->add('font-mono text-base sm:text-sm text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500')
        ->add($invalid ? 'border-red-500' : 'border-zinc-200 border-b-zinc-300/80 dark:border-white/10');
@endphp

<flux:with-field :$attributes>
    <div
        {{ $attributes->class($classes) }}
        @if ($invalid) aria-invalid="true" data-invalid @endif
    >
        <div
            x-data="markdownEditorFormComponent({
                        canAttachFiles: false,
                        isLiveDebounced: false,
                        isLiveOnBlur: false,
                        liveDebounce: '500ms',
                        maxHeight: null,
                        minHeight: null,
                        placeholder: null,
                        state: $wire.entangle('{{ $statePath }}'),
                        toolbarButtons: @js($toolbarButtons),
                        translations: @js(__('labels.fields.markdown_editor')),
                    })"
            wire:ignore
        >
            <textarea x-ref="editor" class="hidden"></textarea>
        </div>
    </div>
</flux:with-field>
