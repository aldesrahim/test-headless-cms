<?php

use App\Models\Category;
use App\Models\Post;
use App\Services\Attachments\AttachmentService;
use App\Services\Posts\PostService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public string $pluralLabel;
    public string $modelLabel;

    public ?Post $record = null;
    public array $state = [];

    public function mount($record): void
    {
        $this->pluralLabel = __('labels.menu.posts.plural');
        $this->modelLabel = str($this->pluralLabel)->singular();

        $this->record = $this->resolveRecord($record);

        $this->fillState();
    }

    public function resolveRecord($record): Post
    {
        if (! $record instanceof Post) {
            $record = app(PostService::class)->find($record);
        }

        $record->loadMissing('categories');

        return $record;
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
        $record = app(PostService::class)->update(
            $this->record,
            [...$this->state, 'status' => $status],
            auth()->user(),
        );

        $this->dispatch('flash-alert:show', [
            'content' => __('labels.form.event.saved', ['label' => $this->modelLabel]),
        ]);
    }

    public function fillState(): void
    {
        $this->state = [
            'id' => $this->record?->id ?? null,
            'slug' => $this->record?->slug ?? null,
            'title' => $this->record?->title ?? null,
            'excerpt' => $this->record?->excerpt ?? null,
            'content' => $this->record?->content ?? null,
            'status' => $this->record?->status ?? null,
            'published_at' => $this->record?->published_at ?? null,
            'categories' => $this->record?->categories->map(fn ($category) => (string) $category->id)->all(),
        ];
    }

    public function markAsDraft(): void
    {
        $this->record->markAsDraft();
    }

    public function delete(): void
    {
        try {
            app(PostService::class)->delete($this->record);

            $this->dispatch('flash-alert:show', [
                'content' => __('labels.form.event.deleted', ['label' => $this->modelLabel]),
            ]);

            $this->redirectRoute('posts.index');
        } catch (Throwable $e) {
            $this->dispatch('flash-alert:show', ['content' => $e->getMessage()]);
        }
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
    <x-panels.heading :heading="__('labels.panel.heading.edit', ['label' => $modelLabel])">
        <x-slot:action>
            <flux:button href="{{ route('posts.index') }}">
                {{ __('labels.form.action.cancel') }}
            </flux:button>

            @if (! $record->isPublished())
                <flux:modal.trigger name="delete-post">
                    <flux:button type="button" variant="danger" icon-trailing="trash">
                        {{ __('labels.form.action.delete') }}
                    </flux:button>
                </flux:modal.trigger>
            @endif
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

                        @if ($attachments = $record->attachments)
                            <div class="flex flex-wrap space-y-2 space-x-3">
                                @foreach ($attachments as $idx => $attachment)
                                    <flux:link :href="$attachment->public_url" class="text-sm" :external="true">
                                        {{ __('Preview uploaded image') }}
                                    </flux:link>
                                @endforeach
                            </div>
                        @endif
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
                    @if (! $record->isPublished())
                        <flux:button type="submit">
                            {{ __('labels.form.action.save') }}
                        </flux:button>
                    @endif

                    <flux:button type="button" variant="primary" wire:click="save(true)">
                        {{ __('Save & Publish') }}
                    </flux:button>

                    @if ($record->isPublished())
                        <flux:button type="button" variant="subtle" wire:click="markAsDraft">
                            {{ __('Mark as Draft') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <livewire:media-picker.modal />

    <flux:modal name="delete-post" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ __('labels.panel.heading.delete', ['label' => $modelLabel]) }}
                </flux:heading>
                <flux:text class="mt-2">
                    <p>{{ __('labels.form.helper.delete.warn') }}</p>
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="button" wire:click="delete" variant="danger">
                    {{ __('labels.form.action.delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
