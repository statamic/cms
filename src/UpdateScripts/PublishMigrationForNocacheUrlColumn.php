<?php

namespace Statamic\UpdateScripts;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PublishMigrationForNocacheUrlColumn extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0')
            && config('statamic.static_caching.nocache', 'cache') === 'database';
    }

    public function update()
    {
        if (! $this->migrationExists('update_nocache_url_column')) {
            $stub = __DIR__.'/stubs/update_nocache_url_column.php.stub';
            $filename = date('Y_m_d_His').'_update_nocache_url_column.php';

            File::put(database_path("migrations/$filename"), File::get($stub));

            $this->console->line(sprintf(
                'Migration <info>database/migrations/%s</info> created successfully.',
                $filename
            ));

            $this->console->line('Run <comment>php artisan migrate</comment> to apply the migration.');
        }
    }

    protected function migrationExists(string $name): bool
    {
        return collect(File::allFiles(database_path('migrations')))
            ->map->getFilename()
            ->filter(fn (string $filename) => Str::contains($filename, $name))
            ->isNotEmpty();
    }
}
