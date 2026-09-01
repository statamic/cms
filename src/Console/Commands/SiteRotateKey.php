<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Config;
use Statamic\Licensing\KeyRotation;
use Statamic\Licensing\SiteKey;

class SiteRotateKey extends Command
{
    use ConfirmableTrait, EnhancesCommands, RunsInPlease;

    protected $signature = 'statamic:site:rotate-key
        { --force : Force the operation to run when in production }';

    protected $description = 'Rotate this site key and keep the old one aliased';

    public function handle(SiteKey $siteKey, KeyRotation $rotation): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $oldKey = Config::getLicenseKey();
        $newKey = $siteKey->generate();

        if ($oldKey && $oldKey !== $newKey) {
            try {
                $rotation->rotate($oldKey, $newKey);
            } catch (\Throwable $e) {
                $this->components->error($e->getMessage());

                return self::FAILURE;
            }
        }

        $siteKey->write($newKey);
        $this->laravel['config']['statamic.system.site_key'] = $newKey;

        $this->components->info('Rotated the site key. Commit .env.example so other environments pick it up.');
        $this->line($newKey);

        return self::SUCCESS;
    }
}
