@props([
    'statePath',
    'label' => 'Select',
])

@php
    $id = md5($statePath);
@endphp

<div
    x-data="{
        isOpen: false,
        trigger: null,
        popover: null,
        selected: $wire.entangle('{{ $statePath }}'),
        init() {
            this.trigger = document.getElementById('multi-select-trigger-{{ $id }}');
            this.popover = document.getElementById('multi-select-popover-{{ $id }}');

            if (this.selected.length > 0) {
                console.log(this.selected);

                this.selected.forEach(selectedValue => {
                    const el = this.popover.querySelectorAll(`[data-value='${selectedValue}']`);

                    if (el && el[0]) {
                        el[0].setAttribute('data-selected', true);
                    }
                })

                console.log(this.selected);
            }

            this.popover.addEventListener('toggle', (e) => {
                this.isOpen = e.newState;
                this.positionPopover();
            })

            document.addEventListener('click', (e) => {
                if (this.isOpen && !this.$root.contains(e.target)) {
                    this.popover.hidePopover();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (this.isOpen && e.key === 'Escape') {
                    this.popover.hidePopover();
                }
            });

            window.addEventListener('resize', () => {
                this.positionPopover();
            });

            window.addEventListener('scroll', () => {
                this.positionPopover();
            }, { passive: true });
        },
        positionPopover() {
            if (!this.isOpen) return;

            const triggerRect = this.trigger.getBoundingClientRect();
            const menuWidth = this.popover.offsetWidth;

            // Calculate position below the button
            let top = triggerRect.bottom + 5;
            let left = triggerRect.left;

            if (left + menuWidth > window.innerWidth) {
                left = window.innerWidth - menuWidth - 8; // 8px padding from edge
            }

            // Apply the calculated position
            this.popover.style.top = `${top}px`;
            this.popover.style.left = `${left}px`;
        },
        toggleSelection(el) {
            const value = el.dataset.value;
            const index = this.selected.indexOf(value);

            if (index === -1) {
                this.selected.push(value);
                el.setAttribute('data-selected', true);
            } else {
                this.selected.splice(index, 1);
                el.removeAttribute('data-selected');
            }
        },
        getLabel() {
            if (this.selected.length > 0) {
                return `${this.selected.length} selected`;
            }

            return '{{ $label }}';
        }
    }"
    wire:ignore.self
>
    <flux:input
        {{ $attributes->only('class') }}
        as="button"
        id="multi-select-trigger-{{ $id }}"
        type="button"
        icon-trailing="chevron-down"
        icon-trailing:variant="micro"
        icon-trailing:class="text-zinc-400"
        popovertarget="multi-select-popover-{{ $id }}"
        popovertargetaction="toggle"
    >
        <span x-text="getLabel()"></span>
    </flux:input>

    <div
        popover="manual"
        class="rounded-lg border border-zinc-200 bg-white p-[.3125rem] shadow-xs dark:border-zinc-600 dark:bg-zinc-700 [:where(&)]:max-h-[20rem] [:where(&)]:min-w-48"
        tabindex="-1"
        role="listbox"
        id="multi-select-popover-{{ $id }}"
    >
        {{ $slot }}
    </div>
</div>
