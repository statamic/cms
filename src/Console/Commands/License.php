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

    protected $description = 'Link this site to a statamic.com account';

    public function handle(DeviceFlow $flow, SiteKey $siteKey, LicenseManager $licenses): int
    {
        $key = Config::getLicenseKey() ?: $siteKey->ensure();

        if ($siteKey->isValid($key)) {
            $this->laravel['config']['statamic.system.site_key'] = $key;
        }

        $licenses->refresh();

        if (($exit = $this->exitIfAlreadyResolved($licenses)) !== null) {
            return $exit;
        }

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
        $this->components->info('Site linked to your account. License status will refresh on the next Outpost check.');

        return self::SUCCESS;
    }

    private function exitIfAlreadyResolved(LicenseManager $licenses): ?int
    {
        if ($licenses->usingLicenseKeyFile()) {
            $this->components->info('This site is using an offline license key file.');

            return self::SUCCESS;
        }

        if ($licenses->requestFailed()) {
            return null;
        }

        return match ($licenses->primaryAction()) {
            null => $this->alreadyResolved('This site is already licensed.'),
            'buy', 'renew' => $this->alreadyResolved('This site is already linked to a statamic.com account.', $licenses->site()->url()),
            'domain' => $this->alreadyResolved('This site is linked to a statamic.com account, but this domain is not on the site record.', $licenses->site()->url()),
            default => null,
        };
    }

    private function alreadyResolved(string $message, ?string $url = null): int
    {
        $this->components->info($message);

        if ($url) {
            $this->line($url);
        }

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
