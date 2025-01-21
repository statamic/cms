<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class SupportDetails extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:support:details';
    protected $description = 'Outputs details helpful for support requests';

    public function handle()
    {
        $this->replaceView();

        try {
            $this->call('about');
        } finally {
            $this->restoreView();
        }

        return static::SUCCESS;
    }

    private function replaceView()
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

    private function restoreView()
    {
        $dir = $this->viewDir();
        app('files')->delete($dir.'/two-column-detail.php');
        app('files')->move($dir.'/two-column-detail.php.bak', $dir.'/two-column-detail.php');
    }

    private function viewDir()
    {
        return base_path('vendor/laravel/framework/src/Illuminate/Console/resources/views/components');
    }
}
