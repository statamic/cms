<?php

namespace Statamic\Dictionaries;

use Illuminate\Support\Str;
use Facades\Statamic\Console\Processes\Process;

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
        $output = Process::run(['locale', '-a']);

        return collect(explode(PHP_EOL, $output))
            ->map(fn ($locale) => Str::before($locale, '.'))
            ->reject(fn ($locale) => in_array($locale, ['C', 'POSIX']))
            ->filter()
            ->sort()
            ->values()
            ->map(fn ($locale) => ['name' => $locale])
            ->all();
    }
}
