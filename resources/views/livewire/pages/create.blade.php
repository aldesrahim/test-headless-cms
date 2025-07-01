<?php

use App\Services\Pages\PageService;
use Livewire\Volt\Component;

new class extends Component {
    public string $pluralLabel;
    public string $modelLabel;

    public array $state = [
        'title' => null,
        'body' => null,
        'status' => null,
    ];

    public function mount(): void
    {
        $this->pluralLabel = __('labels.menu.pages.plural');
        $this->modelLabel = str($this->pluralLabel)->singular();
    }

    public function save(int $status = 0): void
    {
        $record = app(PageService::class)->create([...$this->state, 'status' => $status], auth()->user());

        $this->dispatch('flash-alert:show', [
            'content' => __('labels.form.event.saved', ['label' => $this->modelLabel]),
        ]);
        $this->redirectRoute('pages.index');
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->title(page_title($this->pluralLabel));
    }
}; ?>

<section class="w-full">
    <x-panels.heading :heading="__('labels.panel.heading.create', ['label' => $modelLabel])">
        <x-slot:action>
            <flux:button href="{{ route('pages.index') }}">
                {{ __('labels.form.action.cancel') }}
            </flux:button>
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
</section>
