<?php

use App\Enum\ApplicationStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    /**
     * @return Collection<int, array{label: string, value: string, count: int, color: string, icon: string}>
     */
    #[Computed]
    public function statusCounts(): Collection
    {
        $user = Auth::user();

        $draftCount = $user->applications()
            ->whereDoesntHave('history', fn ($q) => $q->whereNotNull('status'))
            ->count();

        $commentOnlyCount = $user->applications()
            ->whereHas('history')
            ->whereDoesntHave('history', fn ($q) => $q->whereNotNull('status'))
            ->count();

        $statuses = collect([
            [
                'label' => __('Draft'),
                'value' => 'draft',
                'count' => $draftCount,
                'color' => 'zinc',
                'icon' => 'pencil',
            ],
            [
                'label' => __('With Comments'),
                'value' => 'draft',
                'count' => $commentOnlyCount,
                'color' => 'zinc',
                'icon' => 'chat-bubble-left-ellipsis',
                'filter_comment' => true,
            ],
        ]);

        $statusColors = [
            ApplicationStatus::Bookmarked->value => ['color' => 'orange', 'icon' => 'bookmark'],
            ApplicationStatus::Sent->value => ['color' => 'blue', 'icon' => 'paper-airplane'],
            ApplicationStatus::Invited->value => ['color' => 'yellow', 'icon' => 'envelope-open'],
            ApplicationStatus::Accepted->value => ['color' => 'green', 'icon' => 'check-circle'],
            ApplicationStatus::Rejected->value => ['color' => 'red', 'icon' => 'x-circle'],
        ];

        foreach (ApplicationStatus::cases() as $case) {
            $count = $user->applications()
                ->whereHas('history', fn ($q) => $q
                    ->where('status', $case->value)
                    ->whereNotExists(
                        fn ($sub) => $sub->from('applications_history as newer')
                            ->whereColumn('newer.application_id', 'applications_history.application_id')
                            ->whereNotNull('newer.status')
                            ->where(fn ($w) => $w
                                ->whereColumn('newer.created_at', '>', 'applications_history.created_at')
                                ->orWhere(fn ($w2) => $w2
                                    ->whereColumn('newer.created_at', '=', 'applications_history.created_at')
                                    ->whereColumn('newer.id', '>', 'applications_history.id')
                                )
                            )
                    )
                )
                ->count();

            $statuses->push([
                'label' => __($case->name),
                'value' => $case->value,
                'count' => $count,
                'color' => $statusColors[$case->value]['color'],
                'icon' => $statusColors[$case->value]['icon'],
            ]);
        }

        return $statuses;
    }
};
?>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @foreach ($this->statusCounts as $item)
        <a
            href="{{ route('applications.index', ['status' => $item['value']]) }}"
            wire:navigate
            class="group flex flex-col gap-3 rounded-xl border border-neutral-200 bg-white p-5 transition hover:border-neutral-300 hover:shadow-sm dark:border-neutral-700 dark:bg-neutral-900 dark:hover:border-neutral-600"
        >
            <div class="flex items-center justify-between">
                <flux:icon
                    :name="$item['icon']"
                    class="size-5 text-{{ $item['color'] }}-500 dark:text-{{ $item['color'] }}-400"
                />
                <span class="text-2xl font-bold text-neutral-800 dark:text-neutral-100">
                    {{ $item['count'] }}
                </span>
            </div>

            <flux:text class="text-sm font-medium text-neutral-600 group-hover:text-neutral-800 dark:text-neutral-400 dark:group-hover:text-neutral-200">
                {{ $item['label'] }}
            </flux:text>
        </a>
    @endforeach
</div>
