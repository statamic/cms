<?php

namespace Statamic\Console\Commands\Eloquent;

use Statamic\Facades\File;
use Statamic\Support\Str;

use function Laravel\Prompts\spin;

class InstallEloquentEntries extends InstallEloquentRepository
{
    protected $signature = 'eloquent:install-entries
        { --import : Whether existing data should be imported }
        { --without-messages : Disables output messages }';

    protected $handle = 'entries';

    public function handle()
    {
        $shouldImportEntries = $this->shouldImport('entries');

        spin(
            callback: function () use ($shouldImportEntries) {
                $this->switchToEloquentDriver('entries');

                if ($shouldImportEntries) {
                    File::put(
                        config_path('statamic/eloquent-driver.php'),
                        Str::of(File::get(config_path('statamic/eloquent-driver.php')))
                            ->replace("'model' => \Statamic\Eloquent\Entries\EntryModel::class", "'model' => \Statamic\Eloquent\Entries\UuidEntryModel::class")
                            ->__toString()
                    );

                    $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-entries-table-with-string-ids');
                    $this->runArtisanCommand('migrate');

                    $this->runArtisanCommand('statamic:eloquent:import-entries');

                    return;
                }

                if (File::exists(base_path('content/collections/pages/home.md'))) {
                    File::delete(base_path('content/collections/pages/home.md'));
                }

                if (File::exists(base_path('content/trees/collections/pages.yaml'))) {
                    File::put(base_path('content/trees/collections/pages.yaml'), 'tree: {}');
                }

                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-entries-table');
                $this->runArtisanCommand('migrate');
            },
            message: $shouldImportEntries
                ? 'Migrating entries...'
                : 'Migrating and importing entries...'
        );

        $this->infoMessage(
            $shouldImportEntries
                ? 'Configured & imported existing entries'
                : 'Configured entries'
        );
    }

    public function hasBeenMigrated(): bool
    {
        return config('statamic.eloquent-driver.entries.driver') === 'eloquent';
    }
}
