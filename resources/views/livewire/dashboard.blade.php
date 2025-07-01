<?php

use App\Services\Dashboard\DashboardService;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component {
    #[Computed]
    public function counters()
    {
        return app(DashboardService::class)->getCounter();
    }

    public function rendering(\Illuminate\View\View $view): void
    {
        $view->title(page_title(__('Dashboard')));
    }
}; ?>

<section class="w-full">
    <x-panels.heading :heading="__('Dashboard')" :subheading="__('Insights at a Glance')" />

    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        @foreach ($this->counters as $counter)
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 bg-zinc-800/5 p-5 shadow-md dark:border-neutral-700 dark:bg-white/10"
            >
                <p class="text-base font-medium sm:text-lg">{{ $counter['label'] }}</p>
                <h1 class="text-2xl font-medium sm:text-3xl">{{ $counter['count'] }}</h1>
            </div>
        @endforeach
    </div>
</section>
