<?php

namespace Statamic\Console\Commands\Eloquent;

use function Laravel\Prompts\spin;

class InstallEloquentCollections extends InstallEloquentRepository
{
    protected $signature = 'eloquent:install-collections
        { --import : Whether existing data should be imported }
        { --without-messages : Disables output messages }';

    protected $handle = 'collections';

    public function handle()
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-collection-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('collections');
            },
            message: 'Migrating collections...'
        );

        $this->infoMessage('Configured collections');

        if ($this->shouldImport('collections')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-collections --force --only-collections'),
                message: 'Importing existing collections...'
            );

            $this->infoMessage('Imported existing collections');
        }
    }

    public function hasBeenMigrated(): bool
    {
        return config('statamic.eloquent-driver.collections.driver') === 'eloquent';
    }
}
