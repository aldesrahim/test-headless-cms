<?php

use App\Models\Page;
use App\Services\Pages\PageService;
use Livewire\Volt\Component;

new class extends Component {
    public string $pluralLabel;
    public string $modelLabel;

    public ?Page $record = null;
    public array $state = [];

    public function mount($record): void
    {
        $this->pluralLabel = __('labels.menu.pages.plural');
        $this->modelLabel = str($this->pluralLabel)->singular();

        $this->record = $this->resolveRecord($record);

        $this->fillState();
    }

    public function resolveRecord($record): Page
    {
        if (! $record instanceof Page) {
            $record = app(PageService::class)->find($record);
        }

        return $record;
    }

    public function save(int $status = 0): void
    {
        $record = app(PageService::class)->update(
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
            'body' => $this->record?->body ?? null,
            'status' => $this->record?->status ?? null,
            'published_at' => $this->record?->published_at ?? null,
        ];
    }

    public function markAsDraft(): void
    {
        $this->record->markAsDraft();
    }

    public function delete(): void
    {
        try {
            app(PageService::class)->delete($this->record);

            $this->dispatch('flash-alert:show', [
                'content' => __('labels.form.event.deleted', ['label' => $this->modelLabel]),
            ]);

            $this->redirectRoute('pages.index');
        } catch (Throwable $e) {
            $this->dispatch('flash-alert:show', ['content' => $e->getMessage()]);
        }
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->title(page_title($this->pluralLabel));
    }
}; ?>

<section class="w-full">
    <x-panels.heading :heading="__('labels.panel.heading.edit', ['label' => $modelLabel])">
        <x-slot:action>
            <flux:button href="{{ route('pages.index') }}">
                {{ __('labels.form.action.cancel') }}
            </flux:button>

            @if (! $record->isPublished())
                <flux:modal.trigger name="delete-page">
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
                <flux:input label="Title" name="title" wire:model="state.title" />
            </div>

            <div class="space-y-2">
                <x-field.markdown-editor label="Body" name="body" state-path="state.body" />
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

    <flux:modal name="delete-page" class="min-w-[22rem]">
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
