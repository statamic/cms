<?php

namespace Statamic\UpdateScripts;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PublishMigrationForWebauthnTable extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0-beta.4')
            && config('statamic.users.repository', 'file') === 'eloquent';
    }

    public function update()
    {
        if (! $this->migrationExists('statamic_webauthn_table')) {
            $stub = __DIR__.'/../Console/Commands/stubs/auth/statamic_webauthn_table.php.stub';
            $filename = date('Y_m_d_His').'_statamic_webauthn_table.php';

            $contents = File::get($stub);

            $contents = str_replace('WEBAUTHN_TABLE', config('statamic.users.tables.webauthn', 'webauthn'), $contents);

            File::put(database_path("migrations/$filename"), File::get($contents));

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
