<?php

namespace Statamic\Dictionaries;

use Facades\Statamic\Console\Processes\Process;
use Illuminate\Support\Str;

class Locales extends BasicDictionary
{
    protected string $valueKey = 'name';
    protected array $keywords = ['locales'];

    protected function getItemLabel(array $item): string
    {
        return $item['name'];
    }

    protected function getItems(): array
    {
        $output = Process::run($this->buildLocalesCommand());

        return collect(explode(PHP_EOL, $output))
            ->map(fn ($locale) => Str::before($locale, '.'))
            ->reject(fn ($locale) => in_array($locale, ['C', 'POSIX']))
            ->filter()
            ->sort()
            ->values()
            ->map(fn ($locale) => ['name' => $locale])
            ->all();
    }

    private function buildLocalesCommand(): array
    {
        if (windows_os()) {
            return [
                'powershell',
                '-NoProfile',
                '-Command',
                '[System.Globalization.CultureInfo]::GetCultures([System.Globalization.CultureTypes]::InstalledWin32Cultures) | ForEach-Object { $_.Name }',
            ];
        }

        return ['locale', '-a'];
    }
}
