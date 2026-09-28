<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Statamic\Licensing\Outpost;

class ReportThemeUsage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(private $oldTheme, private $newTheme)
    {
    }

    public function handle(Outpost $outpost): void
    {
        if ($outpost->usingLicenseKeyFile()) {
            return;
        }

        $oldThemeId = $this->themeId($this->oldTheme);
        $newThemeId = $this->themeId($this->newTheme);

        if ($oldThemeId === $newThemeId) {
            return;
        }

        if ($newThemeId === null) {
            return;
        }

        Http::post('https://outpost.statamic.com/v3/theme', ['theme' => $newThemeId]);
    }

    private function themeId($theme)
    {
        if (is_array($theme) && isset($theme['id']) && is_int($theme['id'])) {
            return $theme['id'];
        }

        return null;
    }
}
