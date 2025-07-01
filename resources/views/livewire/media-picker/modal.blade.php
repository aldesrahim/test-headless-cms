<?php

use App\Services\Attachments\AttachmentService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component {
    use WithFileUploads;
    use WithPagination;

    public bool $isMultiple = false;
    public string $pageName = 'media-picker-page';

    #[Validate('required|image')]
    public $file;

    public function mount(bool $isMultiple = false)
    {
        $this->isMultiple = $isMultiple;
    }

    public function uploadFile(): void
    {
        $this->validate();

        app(AttachmentService::class)->create($this->file);

        $this->file = null;
    }

    public function deleteFile($id): void
    {
        $service = app(AttachmentService::class);
        $attachment = $service->find($id);

        $service->delete($attachment);

        $this->dispatch('flash-alert:show', ['content' => __('labels.form.event.deleted', ['label' => 'Media'])]);
    }

    #[Computed]
    public function records()
    {
        $pageNumber = $this->paginators[$this->pageName] ??= 1;

        return app(AttachmentService::class)->getPaginated([
            'page' => [
                'number' => $pageNumber,
            ],
        ], $this->pageName);
    }
}; ?>

<flux:modal name="media-picker" class="w-full md:max-w-5xl">
    <div
        x-data="{
        pageName: $wire.entangle('pageName'),
        isMultiple: $wire.entangle('isMultiple'),
        selected: [],
        clear() {
            this.selected = [];
        },
        choose() {
            $dispatch('media-picker:chosen', { selected: this.selected })

            this.clear();
            $flux.modal('media-picker').close();

            $wire.resetPage(this.pageName)
        }
    }"
        class="flex flex-col gap-5"
    >
        <div>
            <flux:heading size="lg">{{ __('Media Picker') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Choose existing media or upload a new one') }}
            </flux:text>
        </div>

        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($this->records as $recordItem)
                @php
                    $key = 'media.item.' . $recordItem->id;
                @endphp

                <div
                    class="relative"
                    wire:key="{{ $key }}"
                    :key="{{ $key }}"
                >
                    @if($recordItem->usage_count === 0)
                        <div class="absolute top-0 right-0 -pt-1 -pr-1">
                            <flux:button
                                type="button"
                                icon="trash"
                                variant="danger"
                                size="xs"
                                wire:click="deleteFile({{ $recordItem->id }})"
                                wire:confirm="{{ __('labels.form.helper.delete.warn') }}"
                            />
                        </div>
                    @endif

                    <label
                        class="flex space-x-3 p-3 cursor-pointer select-none rounded-xl border-1 border-zinc-800/5 dark:border-white/10 has-checked:bg-zinc-800/5 has-checked:dark:bg-white/10"
                        for="{{ $key }}"
                    >
                        <input id="{{ $key }}" x-model="selected" value="{{ $recordItem->id }}"
                               type="{{ $isMultiple ? 'checkbox' : 'radio' }}" hidden="hidden">
                        <div class="w-16 h-16 min-w-16 rounded-md bg-(image:--media-item-url) bg-cover bg-center"
                             style="--media-item-url:url({{ $recordItem->public_url }})"></div>
                        <div class="flex flex-col">
                            <span class="text-base truncate">{{ $recordItem->filename }}</span>
                            <span
                                class="text-sm">{{ __('Size') }}: {{ \Illuminate\Support\Number::fileSize($recordItem->size) }}</span>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>

        <div>
            @include('livewire.media-picker.partials.pagination', ['paginator' => $this->records])

            <flux:separator variant="subtle" class="my-5"/>

            <div class="flex justify-end gap-3 md:items-end">
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <flux:button
                        type="button"
                        @click="choose"
                        variant="primary"
                        x-bind:disabled="selected.length <= 0"
                    >
                        {{ __('Choose selection') }}
                    </flux:button>
                    <span class="text-sm text-zinc-800 dark:text-zinc-300" x-show="selected.length > 1">
                        <span x-text="selected.length"></span> selected
                    </span>
                </div>

                <div>
                    <flux:button
                        type="button"
                        @click="clear"
                        variant="subtle"
                        x-bind:disabled="selected.length <= 0"
                    >{{ __('Clear selection') }}</flux:button>
                </div>
            </div>

            <flux:separator variant="subtle" class="my-5"/>

            <form wire:submit.prevent="uploadFile">
                <div class="flex flex-col justify-start gap-3 md:flex-row md:items-end">
                    <div class="space-y-2">
                        <flux:input type="file" :label="__('Upload new file')" wire:model="file"/>
                    </div>
                    <flux:button variant="primary" class="flex-none" type="submit">
                        {{ __('Upload') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</flux:modal>
