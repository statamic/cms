<?php

namespace Statamic\Console\Commands;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Composer;
use Statamic\Console\RunsInPlease;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'statamic:support:details')]
class SupportDetails extends AboutCommand
{
    use RunsInPlease;

    /**
     * @var string
     */
    protected $signature = 'statamic:support:details
                {--only= : The section to display}
                {--json : Output the information as JSON}';

    /**
     * @var string
     */
    protected $description = 'Outputs details helpful for support requests';

    public function __construct(Composer $composer)
    {
        parent::__construct($composer);
    }

    public function handle()
    {
        $this->replaceView();

        try {
            return parent::handle();
        } finally {
            $this->restoreView();
        }
    }

    /**
     * @param  \Illuminate\Support\Collection  $data
     */
    protected function displayDetail($data): void
    {
        $data->each(function ($data, $section) {
            $this->newLine();

            $this->components->twoColumnDetail('  <fg=green;options=bold>'.$section.'</>');

            $data->pipe(fn ($data) => $section !== 'Environment' ? $data->sort() : $data)
                ->reject(fn ($detail) => $this->shouldExcludeSupportDetail($detail[0]))
                ->each(function ($detail) {
                    [$label, $value] = $detail;

                    $this->components->twoColumnDetail($label, value($value, false));
                });
        });
    }

    /**
     * @param  \Illuminate\Support\Collection  $data
     */
    protected function displayJson($data): void
    {
        $data = $data->map(fn ($sectionData) => $sectionData
            ->reject(fn ($detail) => $this->shouldExcludeSupportDetail($detail[0]))
            ->values());

        parent::displayJson($data);
    }

    private function shouldExcludeSupportDetail(string $label): bool
    {
        return in_array($label, ['Application Name', 'URL'], true);
    }

    private function replaceView(): void
    {
        $view = <<<'EOT'
<div class="flex">
    <?php echo htmlspecialchars($first) ?><?php if ($second !== '') { ?>: <?php echo htmlspecialchars($second) ?> <?php } ?>
</div>
EOT;

        $dir = $this->viewDir();
        app('files')->move($dir.'/two-column-detail.php', $dir.'/two-column-detail.php.bak');
        app('files')->put($dir.'/two-column-detail.php', $view);
    }

    private function restoreView(): void
    {
        $dir = $this->viewDir();
        app('files')->delete($dir.'/two-column-detail.php');
        app('files')->move($dir.'/two-column-detail.php.bak', $dir.'/two-column-detail.php');
    }

    private function viewDir(): string
    {
        return base_path('vendor/laravel/framework/src/Illuminate/Console/resources/views/components');
    }
}
