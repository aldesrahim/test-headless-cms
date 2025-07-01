<?php

use App\Models\Category;
use App\Services\Attachments\AttachmentService;
use App\Services\Posts\PostService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public string $pluralLabel;
    public string $modelLabel;

    public array $state = [
        'title' => null,
        'categories' => [],
        'content' => null,
        'excerpt' => null,
        'attachment' => null,
        'status' => null,
    ];

    public function mount(): void
    {
        $this->pluralLabel = __('labels.menu.posts.plural');
        $this->modelLabel = str($this->pluralLabel)->singular();
    }

    #[Computed]
    public function categories(): Collection
    {
        return Category::query()
            ->orderBy('slug')
            ->get();
    }

    public function save(int $status = 0): void
    {
        $record = app(PostService::class)->create([...$this->state, 'status' => $status], auth()->user());

        $this->dispatch('flash-alert:show', [
            'content' => __('labels.form.event.saved', ['label' => $this->modelLabel]),
        ]);
        $this->redirectRoute('posts.index');
    }

    #[On('media-picker:chosen')]
    public function mediaPickerChosen($selected)
    {
        $this->state['attachment'] = $selected;
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->title(page_title($this->pluralLabel));
    }
}; ?>

<section class="w-full">
    <x-panels.heading :heading="__('labels.panel.heading.create', ['label' => $modelLabel])">
        <x-slot:action>
            <flux:button href="{{ route('posts.index') }}">
                {{ __('labels.form.action.cancel') }}
            </flux:button>
        </x-slot>
    </x-panels.heading>

    <form wire:submit.prevent="save">
        <div class="max-w-2xl space-y-6">
            <div class="space-y-2">
                <p
                    class="inline-flex items-center text-sm font-medium [:where(&)]:text-zinc-800 [:where(&)]:dark:text-white"
                >
                    {{ __('Banner Image') }}
                </p>

                <div class="w-full">
                    <div class="flex flex-col gap-3">
                        <flux:modal.trigger name="media-picker">
                            <flux:button>{{ __('Media Picker') }}</flux:button>
                        </flux:modal.trigger>
                    </div>

                    @if (isset($state['attachment']))
                        <p class="text-sm font-medium text-zinc-800 dark:text-zinc-300">You've selected 1 file</p>
                    @endif
                </div>

                @error('attachment')
                    <div
                        role="alert"
                        aria-live="polite"
                        aria-atomic="true"
                        class="mt-3 text-sm font-medium text-red-500 dark:text-red-400"
                    >
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="space-y-2">
                <flux:input label="Title" name="title" wire:model="state.title" />
            </div>

            <div class="space-y-2">
                <flux:with-field name="categories" label="Categories">
                    <x-field.multi-select state-path="state.categories">
                        @foreach ($this->categories as $categoryItem)
                            <x-field.multi-select.option
                                :value="$categoryItem->id"
                                :label="$categoryItem->name"
                                ::key="category.option.{{ $categoryItem->id }}"
                                wire:key="category.option.{{ $categoryItem->id }}"
                            />
                        @endforeach
                    </x-field.multi-select>
                </flux:with-field>
            </div>

            <div class="space-y-2">
                <flux:textarea label="Excerpt" name="excerpt" wire:model="state.excerpt" />
            </div>

            <div class="space-y-2">
                <x-field.markdown-editor label="Content" name="content" state-path="state.content" />
            </div>

            <div class="flex">
                <div class="space-x-2">
                    <flux:button type="submit">
                        {{ __('labels.form.action.save') }}
                    </flux:button>

                    <flux:button type="button" variant="primary" wire:click="save(true)">
                        {{ 'Save & Publish' }}
                    </flux:button>
                </div>
            </div>
        </div>
    </form>

    <livewire:media-picker.modal />
</section>
