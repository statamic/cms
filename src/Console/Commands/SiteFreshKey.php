<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Licensing\SiteKey;

class SiteFreshKey extends Command
{
    use ConfirmableTrait, EnhancesCommands, RunsInPlease;

    protected $signature = 'statamic:site:fresh-key
        { --force : Force the operation to run when in production }';

    protected $description = 'Generate a new site key (for cloned templates)';

    public function handle(SiteKey $siteKey): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $key = $siteKey->write($siteKey->generate());

        $this->laravel['config']['statamic.system.site_key'] = $key;

        $this->components->info('Generated a new site key.');
        $this->line($key);

        return self::SUCCESS;
    }
}
