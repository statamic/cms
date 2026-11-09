<?php

namespace Statamic\Http\Controllers\CP\Ai;

use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Statamic\CP\Column;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;

/**
 * Prototype (phase 2): Tools → AI Reports, where the runs of the scheduled prompts on Settings → AI are kept, like a form's submissions.
 *
 * Reports lists the scheduled prompts, each one lists its runs, and each run is a report of what it found. Nothing runs yet: the runs
 * are made up, one for each time the prompt would have run lately, and the example health report has made-up findings.
 */
class AiReportsController extends CpController
{
    public function index()
    {
        $prompts = collect(AiSettingsController::scheduledPrompts())->map(fn ($prompt) => [
            'id' => $prompt['handle'],
            'title' => $prompt['name'],
            'schedule' => $this->schedule($prompt),
            'last_run' => ($last = $this->runs($prompt)->first()) ? $last['date'] : null,
            'reports' => $this->runs($prompt)->count(),
            'show_url' => cp_route('ai.reports.show', $prompt['handle']),
        ]);

        return Inertia::render('ai-reports/Index', [
            'prompts' => $prompts->all(),
            'columns' => [
                Column::make('title')->label(__('Name')),
                Column::make('schedule')->label(__('Runs')),
                Column::make('last_run')->label(__('Last Run')),
                Column::make('reports')->label(__('Reports')),
            ],
            'settingsUrl' => cp_route('ai.edit'),
        ]);
    }

    public function show($prompt)
    {
        $prompt = $this->prompt($prompt);

        return Inertia::render('ai-reports/Show', [
            'title' => $prompt['name'],
            'schedule' => $this->schedule($prompt),
            'runs' => $this->runs($prompt)->map(fn ($run) => [
                'id' => $run['id'],
                'date' => $run['date'],
                'found' => $run['found'],
                'breakdown' => $run['breakdown'],
                'url' => cp_route('ai.reports.report', [$prompt['handle'], $run['id']]),
            ])->all(),
            'columns' => [
                Column::make('date')->label(__('Date')),
                Column::make('found')->label(__('Found')),
                Column::make('breakdown')->label(__('Summary')),
            ],
        ]);
    }

    public function report($prompt, $run)
    {
        $prompt = $this->prompt($prompt);
        $runs = $this->runs($prompt);
        $index = $runs->search(fn ($item) => $item['id'] === $run);
        abort_if($index === false, 404);
        $report = $runs[$index];

        return Inertia::render('ai-reports/Report', [
            'promptTitle' => $prompt['name'],
            'instructions' => $prompt['instructions'] ?? '',
            'date' => $report['date'],
            'summary' => $report['summary'],
            'sections' => $report['sections'],
            'listUrl' => cp_route('ai.reports.show', $prompt['handle']),
            // Newer and older runs, to step through them as you would submissions
            'newerUrl' => ($newer = $runs[$index - 1] ?? null) ? cp_route('ai.reports.report', [$prompt['handle'], $newer['id']]) : null,
            'olderUrl' => ($older = $runs[$index + 1] ?? null) ? cp_route('ai.reports.report', [$prompt['handle'], $older['id']]) : null,
        ]);
    }

    private function prompt(string $handle): array
    {
        return collect(AiSettingsController::scheduledPrompts())->firstWhere('handle', $handle) ?? abort(404);
    }

    // "Every Monday at 09:00"
    private function schedule(array $prompt): string
    {
        $time = $prompt['time'] ?? '09:00';

        return match ($prompt['repeat'] ?? 'weekly') {
            'daily' => __('Every day at :time', ['time' => $time]),
            'monthly' => __('The 1st of every month at :time', ['time' => $time]),
            default => __('Every :day at :time', ['day' => __(ucfirst($prompt['day'] ?? 'monday')), 'time' => $time]),
        };
    }

    // The last six times it would have run, newest first. A prompt that's switched off (paused) stopped two runs ago.
    private function runs(array $prompt)
    {
        [$hour, $minute] = array_map('intval', explode(':', $prompt['time'] ?? '09:00')) + [1 => 0];
        $repeat = $prompt['repeat'] ?? 'weekly';
        $day = $prompt['day'] ?? 'monday';

        // Back to the latest time it ran
        $date = Carbon::now()->setTime($hour, $minute);
        while ($date->isFuture() || ($repeat === 'weekly' && strtolower($date->format('l')) !== $day) || ($repeat === 'monthly' && $date->day !== 1)) {
            $date->subDay();
        }

        $dates = collect(range(0, 5))->map(fn ($i) => match ($repeat) {
            'daily' => $date->copy()->subDays($i),
            'monthly' => $date->copy()->subMonthsNoOverflow($i),
            default => $date->copy()->subWeeks($i),
        });

        if (! ($prompt['enabled'] ?? true)) {
            $dates = $dates->slice(2)->values();
        }

        $isHealthReport = $prompt['handle'] === 'weekly-content-health-report';

        return $dates->map(fn ($date, $i) => [
            'id' => $date->format('Y-m-d'),
            'date' => $date->format('l j F Y, H:i'),
            ...($isHealthReport ? $this->healthReport($i) : $this->otherReport()),
        ]);
    }

    // What the example health report found each week. The newest matches the AI chat's demo of it (see mock-runs.js). Older weeks found
    // a little more, as the team has been fixing things.
    private function healthReport(int $weeksAgo): array
    {
        $counts = [
            [3, 12, 4, 2, 6],
            [5, 12, 4, 3, 7],
            [2, 13, 6, 3, 7],
            [4, 13, 6, 1, 9],
            [6, 14, 7, 2, 9],
            [3, 15, 7, 2, 11],
        ][$weeksAgo] ?? [3, 15, 7, 2, 11];

        // Most important first: what's broken for visitors, then what hurts accessibility and search, then housekeeping (minor), which the
        // summary only mentions in passing
        $sections = [
            [
                'heading' => __('Broken links'),
                // Which of the week's counts is this one's
                'count' => 0,
                'why' => __('Fix these first: visitors hit a dead end.'),
                // The AI chat can fix these for you: what it's asked (see the broken links scenario in mock-runs.js)
                'fix' => __('Fix the broken links from this week’s content health report'),
                'items' => [
                    ['page' => 'Our summer sale', 'detail' => '“Shop loungers” links to /garden/loungers, which isn’t found'],
                    ['page' => 'Our summer sale', 'detail' => '“Delivery” links to /delivery, which isn’t found'],
                    ['page' => 'Delivery areas', 'detail' => 'The Royal Mail rates link doesn’t respond'],
                    ['page' => 'About', 'detail' => 'The Instagram link goes to an account that no longer exists'],
                    ['page' => 'Contact', 'detail' => '“Find us” links to an old Google Maps address'],
                    ['page' => 'Spring planting guide', 'detail' => '“Bulbs” links to /garden/bulbs, which isn’t found'],
                ],
            ],
            [
                'heading' => __('Images without alt text'),
                // Which of the week's counts is this one's
                'count' => 2,
                'why' => __('Screen readers can’t describe them.'),
                'fix' => __('Write alt text for the images that have none'),
                'items' => collect(['golden-retriever.png' => 'Our summer sale', 'n64.jpg' => 'Retro games range', 'sega-dreamcast.jpg' => 'Retro games range', 'fanny-pack.webp' => 'Festival kit', 'hammock.jpg' => 'Our summer sale', 'dining-set.jpg' => 'Garden range', 'parasol.jpg' => 'Garden range'])
                    ->map(fn ($page, $file) => ['page' => $page, 'detail' => $file])->values()->all(),
            ],
            [
                'heading' => __('Meta descriptions missing or too long'),
                // Which of the week's counts is this one's
                'count' => 4,
                'why' => __('They shape how pages show in search results.'),
                'fix' => __('Write meta descriptions for the pages missing one or over the limit'),
                'items' => collect(['Our summer sale' => '182 characters', 'Garden range' => 'Missing', 'Festival kit' => 'Missing', 'Retro games range' => '171 characters', 'Size guide' => 'Missing', 'Gift cards' => '160 characters', 'Careers' => 'Missing', 'Press' => '190 characters', 'Warranty' => 'Missing', 'Wholesale' => 'Missing', 'Affiliates' => '158 characters'])
                    ->map(fn ($detail, $page) => ['page' => $page, 'detail' => $detail])->values()->all(),
            ],
            [
                'heading' => __('Drafts untouched for a month'),
                // Which of the week's counts is this one's
                'count' => 3,
                'why' => null,
                // How the summary names them in passing
                'label' => __('drafts untouched for a month'),
                'minor' => true,
                'items' => [
                    ['page' => 'Spring planting guide', 'detail' => 'Last edited 14 September'],
                    ['page' => 'Meet the team', 'detail' => 'Last edited 2 October'],
                    ['page' => 'Winter opening hours', 'detail' => 'Last edited 6 October'],
                ],
            ],
            [
                'heading' => __('Not updated in over a year'),
                // Which of the week's counts is this one's
                'count' => 1,
                'why' => null,
                'label' => __('pages not updated in over a year'),
                'minor' => true,
                'items' => collect(['Delivery areas' => 'March 2025', 'Returns policy' => 'April 2025', 'Shipping policy' => 'May 2025', 'Careers' => 'June 2025', 'Our story' => 'June 2025', 'Size guide' => 'July 2025', 'Gift cards' => 'August 2025', 'Care instructions' => 'August 2025', 'Store locations' => 'September 2025', 'Trade accounts' => 'September 2025', 'Press' => 'October 2025', 'Sustainability' => 'October 2025', 'Warranty' => 'November 2025', 'Wholesale' => 'November 2025', 'Affiliates' => 'November 2025'])
                    ->map(fn ($when, $page) => ['page' => $page, 'detail' => __('Last updated :when', ['when' => $when])])->values()->all(),
            ],
        ];

        $sections = collect($sections)->map(fn ($section) => [
            ...$section,
            'items' => collect($section['items'])->take($counts[$section['count']])->map(fn ($item) => [...$item, 'url' => $this->entryUrl($item['page'])])->all(),
        ]);

        $found = array_sum($counts);

        return [
            'summary' => trans_choice(':count thing needs attention|:count things need attention', $found, ['count' => $found]),
            'found' => $found,
            // "3 broken links · 4 no alt text · …", most important first, for the list of runs
            'breakdown' => collect([0 => __('broken links'), 2 => __('no alt text'), 4 => __('meta'), 3 => __('stale drafts'), 1 => __('old pages')])
                ->map(fn ($label, $i) => "{$counts[$i]} {$label}")->implode(' · '),
            'sections' => $sections->all(),
        ];
    }

    // Any other scheduled prompt follows its instructions, vaguely
    private function otherReport(): array
    {
        return [
            'summary' => __('Nothing needs attention'),
            'found' => 0,
            'breakdown' => '',
            'sections' => [],
        ];
    }

    // A link to the page, where there's an entry with that title
    private function entryUrl(string $title): ?string
    {
        static $urls = [];

        return $urls[$title] ??= Entry::query()->where('title', $title)->first()?->editUrl();
    }
}
