@props([
    'currentPageOptionProperty' => 'tableRecordsPerPage',
    'paginator',
    'pageOptions' => [],
    
])

@php
    use Illuminate\Contracts\Pagination\CursorPaginator;
    use Illuminate\Pagination\LengthAwarePaginator;

    $isSimple = ! $paginator instanceof LengthAwarePaginator;
@endphp

<nav {{ $attributes->merge(['class' => 'grid grid-cols-[1fr_auto_1fr] items-center gap-x-3 p-4']) }}>
    @if (! $paginator->onFirstPage())
        @php
            if ($paginator instanceof CursorPaginator) {
                $wireClickAction = "setPage('{$paginator->previousCursor()->encode()}', '{$paginator->getCursorName()}')";
            } else {
                $wireClickAction = "previousPage('{$paginator->getPageName()}')";
            }
        @endphp

        <flux:button
            :wire:click="$wireClickAction"
            :wire:key="$this->getId() . '.pagination.previous'"
            class="justify-self-start md:hidden"
            size="sm"
        >
            Previous
        </flux:button>
    @endif

    @if (! $isSimple)
        <div class="hidden text-sm font-medium whitespace-nowrap text-zinc-500 md:block dark:text-zinc-400">
            {{
                trans_choice('labels.pagination.overview', $paginator->total(), [
                    'first' => \Illuminate\Support\Number::format($paginator->firstItem() ?? 0),
                    'last' => \Illuminate\Support\Number::format($paginator->lastItem() ?? 0),
                    'total' => \Illuminate\Support\Number::format($paginator->total()),
                ])
            }}
        </div>
    @endif

    <div class="col-start-2 justify-self-center">
        @if (count($pageOptions) > 1)
            <flux:select size="sm" :wire:model.live="$currentPageOptionProperty" placeholder="Per page">
                @foreach ($pageOptions as $option)
                    <flux:select.option :value="$option">
                        {{ $option === 'all' ? 'All' : $option }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        @endif
    </div>

    @if ($paginator->hasMorePages())
        @php
            if ($paginator instanceof CursorPaginator) {
                $wireClickAction = "setPage('{$paginator->nextCursor()->encode()}', '{$paginator->getCursorName()}')";
            } else {
                $wireClickAction = "nextPage('{$paginator->getPageName()}')";
            }
        @endphp

        <flux:button
            :wire:click="$wireClickAction"
            :wire:key="$this->getId() . '.pagination.next'"
            class="col-start-3 justify-self-end md:hidden"
            size="sm"
        >
            Next
        </flux:button>
    @endif

    @if (! $isSimple && $paginator->hasPages())
        <div class="hidden justify-self-end md:flex">
            <flux:button.group>
                @if (! $paginator->onFirstPage())
                    <flux:button
                        :wire:click="'previousPage(\'' . $paginator->getPageName() . '\')'"
                        :wire:key="$this->getId() . '.pagination.previous'"
                        size="sm"
                        icon="chevron-left"
                    />
                @endif

                @foreach ($paginator->render()->offsetGet('elements') as $element)
                    @if (is_string($element))
                        <flux:button disabled size="sm">{{ $element }}</flux:button>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <flux:button
                                :wire:click="'gotoPage(' . $page . ', \'' . $paginator->getPageName() . '\')'"
                                :wire:key="$this->getId() . '.pagination.' . $paginator->getPageName() . '.' . $page"
                                size="sm"
                                :variant="$page === $paginator->currentPage() ? 'primary' : 'outline'"
                                color="neutral"
                            >
                                {{ $page }}
                            </flux:button>
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <flux:button
                        :wire:click="'nextPage(\'' . $paginator->getPageName() . '\')'"
                        :wire:key="$this->getId() . '.pagination.next'"
                        size="sm"
                        icon="chevron-right"
                    />
                @endif
            </flux:button.group>
        </div>
    @endif
</nav>
