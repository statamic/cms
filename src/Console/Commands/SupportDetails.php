<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Console\AboutCommand;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Addon;
use Statamic\Statamic;

class SupportDetails extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:support:details';
    protected $description = 'Outputs details helpful for support requests';

    public function handle()
    {
        return class_exists(AboutCommand::class)
            ? $this->handleUsingAboutCommand()
            : $this->handleUsingStatamic();
    }

    private function handleUsingStatamic()
    {
        $this->line(sprintf('<info>Statamic</info> %s %s', Statamic::version(), Statamic::pro() ? 'Pro' : 'Solo'));
        $this->line('<info>Laravel</info> '.Application::VERSION);
        $this->line('<info>PHP</info> '.phpversion());
        $this->line(sprintf('<info>Stache Watcher</info> %s', config('statamic.stache.watcher') ? 'Enabled' : 'Disabled'));
        $this->line(sprintf('<info>Static Caching</info> %s', config('statamic.static_caching.strategy') ?: 'Disabled'));
        $this->addons();

        return static::SUCCESS;
    }

    private function addons()
    {
        $addons = Addon::all();

        if ($addons->isEmpty()) {
            return $this->line('No addons installed');
        }

        foreach ($addons as $addon) {
            $this->line(sprintf('<info>%s</info> %s', $addon->package(), $addon->version()));
        }
    }

    private function handleUsingAboutCommand()
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
