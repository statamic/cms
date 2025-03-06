<?php

namespace Statamic\UpdateScripts;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Listeners\Concerns\GetsItemsContainingData;

class AddTimezoneConfigOptions extends UpdateScript
{
    use GetsItemsContainingData;

    public function shouldUpdate($newVersion, $oldVersion): bool
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update(): void
    {
        if (! File::exists($path = app()->configPath('statamic/system.php'))) {
            return;
        }

        $systemConfig = File::get($path);

        if (Str::contains($systemConfig, 'display_timezone')) {
            return;
        }

        $lineNumberOfDateFormatOption = collect(explode("\n", $systemConfig))
            ->filter(fn ($line) => Str::contains($line, 'date_format'))
            ->keys()
            ->first();

        $stub = Str::of(File::get(__DIR__.'/stubs/system_timezone_config.php.stub'))
            ->replace('TIMEZONE', config('app.timezone'))
            ->__toString();

        $systemConfig = Str::of($systemConfig)
            ->explode("\n")
            ->put($lineNumberOfDateFormatOption + 1, $stub)
            ->implode("\n");

        File::put(app()->configPath('statamic/system.php'), $systemConfig);
    }
}
