<?php

use App\Exceptions\ConstraintViolationException;
use App\Models\Category;
use App\Services\Categories\CategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public int $tableRecordsPerPage = 10;

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

    public function save(): void
    {
        if ($this->record) {
            $this->record = app(CategoryService::class)->update($this->record, $this->state);
        } else {
            $this->record = app(CategoryService::class)->create($this->state);
        }

        $this->dispatch('flash-alert:show', ['content' => __('labels.form.event.saved', ['label' => $this->modelLabel])]);
        $this->resetState();

        $this->modal('delete-category')->close();
        $this->modal('manage-category')->close();
    }

    public function create(): void
    {
        $this->resetState();

        $this->modal('delete-category')->close();
        $this->modal('manage-category')->show();
    }

    public function edit($record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->fillState();

        $this->modal('delete-category')->close();
        $this->modal('manage-category')->show();
    }

    public function delete($record, $confirmed = false): void
    {
        $this->record = $this->resolveRecord($record);

        if (!$confirmed) {
            $this->modal('delete-category')->show();

            return;
        }

        if (empty($this->record)) {
            throw new ModelNotFoundException;
        }

        try {
            app(CategoryService::class)->delete($this->record);

            $this->dispatch('flash-alert:show', ['content' => __('labels.form.event.deleted', ['label' => $this->modelLabel])]);
            $this->resetPage();
        } catch (ConstraintViolationException $e) {
            $this->dispatch('flash-alert:show', ['content' => $e->getMessage()]);
        } finally {
            $this->resetState();

            $this->modal('delete-category')->close();
            $this->modal('manage-category')->close();
        }
    }

    public function resetState(): void
    {
        $this->record = null;
        $this->fillState();
    }

    public function fillState(): void
    {
        $this->state = [
            'id' => $this->record?->id,
            'name' => $this->record?->name,
            'slug' => $this->record?->slug,
        ];
    }

    #[Computed]
    public function records()
    {
        $pageNumber = $this->paginators[$pageName = 'page'] ??= 1;

        return app(CategoryService::class)->getPaginated([
            'page' => [
                'number' => $pageNumber,
                'size' => $this->tableRecordsPerPage,
            ],
        ], $pageName);
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->title(
            page_title($this->pluralLabel)
        );
    }
}; ?>

<section class="w-full">
    <x-panels.heading :heading="$pluralLabel" :subheading="__('Manage categories for posts')">
        <x-slot:action>
            <flux:button wire:click="create">{{ __('labels.form.action.add', ['label' => $modelLabel]) }}</flux:button>
        </x-slot:action>
    </x-panels.heading>

    <div>
        <x-table>
            <x-slot:columns>
                <x-table.column>Slug</x-table.column>
                <x-table.column>Name</x-table.column>
                <x-table.column.action/>
            </x-slot:columns>

            <x-slot:rows>
                @foreach($this->records as $record)
                    <x-table.row>
                        <x-table.cell>{{ $record->slug }}</x-table.cell>
                        <x-table.cell>{{ $record->name }}</x-table.cell>
                        <x-table.cell>
                            <flux:button.group>
                                <flux:button variant="outline" size="sm" wire:click="edit({{ $record->id }})">
                                    {{ __('labels.form.action.edit') }}
                                </flux:button>
                                <flux:button variant="danger" size="sm" wire:click="delete({{ $record->id }})">
                                    {{ __('labels.form.action.delete') }}
                                </flux:button>
                            </flux:button.group>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-slot:rows>

            <x-slot:pagination>
                <x-table.pagination
                    :paginator="$this->records"
                    :page-options="[10, 15, 20, 50]"
                />
            </x-slot:pagination>
        </x-table>
    </div>

    <flux:modal name="manage-category" class="md:w-96">
        <form wire:submit.prevent="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        @if (filled($record))
                            {{ __('labels.panel.heading.edit', ['label' => $record?->name]) }}
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

                    <div class="space-x-2">
                        <flux:button type="submit" variant="primary">
                            {{ __('labels.form.action.save') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-category" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading
                    size="lg">{{ __('labels.panel.heading.delete', ['label' => $record?->name]) }}</flux:heading>
                <flux:text class="mt-2">
                    <p>{{ __('labels.form.helper.delete.warn') }}</p>
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer/>
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="button" wire:click="delete({{ $record?->id }}, 1)" variant="danger">
                    {{ __('labels.form.action.delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
