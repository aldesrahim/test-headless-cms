<?php

use App\Enums\DateFormat;
use App\Services\Posts\PostService;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public int $tableRecordsPerPage = 10;

    public string $pluralLabel;
    public string $modelLabel;

    public function mount(): void
    {
        $this->pluralLabel = __('labels.menu.posts.plural');
        $this->modelLabel = str($this->pluralLabel)->singular();
    }

    #[Computed]
    public function records()
    {
        $pageNumber = $this->paginators[($pageName = 'page')] ??= 1;

        return app(PostService::class)->getPaginated(
            [
                'page' => [
                    'number' => $pageNumber,
                    'size' => $this->tableRecordsPerPage,
                ],
                'sort' => ['by' => 'created_at', 'direction' => 'desc'],
            ],
            $pageName,
        );
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->title(page_title($this->pluralLabel));
    }
}; ?>

<section class="w-full">
    <x-panels.heading :heading="$pluralLabel" :subheading="__('Manage and publish posts')">
        <x-slot:action>
            <flux:button href="{{ route('posts.create') }}" type="button">
                {{ __('labels.form.action.add', ['label' => $modelLabel]) }}
            </flux:button>
        </x-slot>
    </x-panels.heading>

    <div>
        <x-table>
            <x-slot:columns>
                <x-table.column>{{ __('Slug') }}</x-table.column>
                <x-table.column>{{ __('Title') }}</x-table.column>
                <x-table.column>{{ __('Published') }}?</x-table.column>
                <x-table.column.action />
            </x-slot>

            <x-slot:rows>
                @foreach ($this->records as $recordItem)
                    <x-table.row>
                        <x-table.cell>{{ $recordItem->slug }}</x-table.cell>
                        <x-table.cell>{{ $recordItem->title }}</x-table.cell>
                        <x-table.cell>
                            {{ $recordItem->published_at?->translatedFormat(DateFormat::ReadableDateTime->value) ?? __('Draft') }}
                        </x-table.cell>
                        <x-table.cell>
                            <flux:button.group>
                                <flux:button
                                    href="{{ route('posts.edit', ['record' => $recordItem->id]) }}"
                                    variant="outline"
                                    size="sm"
                                    wire:key="table.action.edit.{{ $recordItem->id }}"
                                >
                                    {{ __('labels.form.action.edit') }}
                                </flux:button>
                            </flux:button.group>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-slot>

            <x-slot:pagination>
                <x-table.pagination :paginator="$this->records" :page-options="[10, 15, 20, 50]" />
            </x-slot>
        </x-table>
    </div>
</section>
