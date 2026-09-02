<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Config;
use Statamic\Licensing\DeviceFlow;
use Statamic\Licensing\LicenseManager;
use Statamic\Licensing\SiteKey;

class License extends Command
{
    use EnhancesCommands, RunsInPlease;

    protected $signature = 'statamic:license
        { --poll-once : Check status once and exit (for tests) }';

    protected $description = 'Claim this site and attach a Statamic license';

    public function handle(DeviceFlow $flow, SiteKey $siteKey, LicenseManager $licenses): int
    {
        $key = Config::getLicenseKey() ?: $siteKey->ensure();

        $this->laravel['config']['statamic.system.site_key'] = $key;

        try {
            $session = $flow->start($key, $this->host());
        } catch (\Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Open this URL and confirm the code:');
        $this->line($session['url']);
        $this->newLine();
        $this->line('  '.$session['code']);
        $this->newLine();

        $status = $this->waitForCompletion($flow, $session);

        if ($status !== 'complete') {
            $this->components->warn('Still waiting. Run this command again after you finish on statamic.com.');

            return self::FAILURE;
        }

        $licenses->refresh();
        $this->components->info('Site connected. License status will refresh on the next Outpost check.');

        return self::SUCCESS;
    }

    private function waitForCompletion(DeviceFlow $flow, array $session): string
    {
        $attempts = $this->option('poll-once') ? 1 : 60;
        $status = 'pending';

        for ($i = 0; $i < $attempts; $i++) {
            if ($i > 0 && ! $this->option('poll-once')) {
                sleep($session['interval']);
            }

            try {
                $status = $flow->poll($session['device_code'])['status'];
            } catch (\Throwable $e) {
                $this->components->error($e->getMessage());

                return 'error';
            }

            if (in_array($status, ['complete', 'expired'], true)) {
                return $status;
            }
        }

        return $status;
    }

    private function host(): string
    {
        return parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';
    }
}
