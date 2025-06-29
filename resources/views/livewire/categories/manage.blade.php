<?php

use App\Models\Category;
use App\Services\Categories\CategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Volt\Component;

new class extends Component {
    public string $pluralLabel;
    public string $modelLabel;

    public ?Category $record = null;
    public array $state = [];

    public function mount($record = null)
    {
        $this->pluralLabel = __('labels.menu.categories.plural');
        $this->modelLabel = str($this->pluralLabel)->singular();

        if ($record) {
            $this->record = $this->resolveRecord($record);
            $this->fillState();
        }
    }

    public function resolveRecord($record): Category
    {
        if ($record instanceof Category) {
            return $record;
        }

        return app(CategoryService::class)->find($record);
    }

    public function saveRecord(): void
    {
        if ($this->record) {
            $this->record = app(CategoryService::class)->update($this->record, $this->state);
        } else {
            $this->record = app(CategoryService::class)->create($this->state);
        }

        $this->dispatch('flash-alert:show', ['content' => __('labels.form.event.saved', ['label' => $this->modelLabel])]);
        $this->resetState();
    }

    public function deleteRecord(): void
    {
        if (empty($this->record)) {
            throw new ModelNotFoundException;
        }

        app(CategoryService::class)->delete($this->record);

        $this->resetState();
    }

    public function resetState(): void
    {
        $this->record = null;
        $this->fillState();
        $this->modal('manage-category')->close();
    }

    public function fillState(): void
    {
        $this->state = [
            'id' => $this->record?->id,
            'name' => $this->record?->name,
            'slug' => $this->record?->slug,
        ];
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->title($this->pluralLabel);
    }
}; ?>

<section class="w-full">
    <x-panels.heading :heading="$pluralLabel" :subheading="__('Manage categories for posts')">
        <x-slot:action>
            <flux:modal.trigger name="manage-category">
                <flux:button>{{ __('labels.form.button.add', ['label' => $modelLabel]) }}</flux:button>
            </flux:modal.trigger>
        </x-slot:action>
    </x-panels.heading>

    <div>
        <!-- Table here -->
    </div>

    <flux:modal name="manage-category" class="md:w-96">
        <form wire:submit.prevent="saveRecord">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        @if (isset($record))
                            {{ __('labels.panel.heading.edit', ['label' => $modelLabel]) }}
                        @else
                            {{ __('labels.panel.heading.create', ['label' => $modelLabel]) }}
                        @endif
                    </flux:heading>
                </div>

                <div class="space-y-2">
                    <flux:input label="Name" wire:model="state.name" placeholder="Tech, News, Laravel, etc"/>
                    @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex">
                    <flux:spacer/>

                    <div class="space-x">
                        <flux:button type="submit" variant="primary">
                            {{ __('labels.form.button.save') }}
                        </flux:button>

                        @isset($record)
                            <flux:button
                                type="button"
                                variant="danger"
                                wire:click="deleteRecord"
                                :wire:confirm="__('This action can not be undone, continue?')"
                            >
                                {{ __('labels.form.button.delete') }}
                            </flux:button>
                        @endisset
                    </div>
                </div>
            </div>
        </form>
    </flux:modal>
</section>
