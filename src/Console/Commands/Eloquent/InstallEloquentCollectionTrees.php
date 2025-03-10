<?php

namespace Statamic\Console\Commands\Eloquent;

use function Laravel\Prompts\spin;

class InstallEloquentCollectionTrees extends InstallEloquentRepository
{
    protected $signature = 'eloquent:install-collection-trees
        { --import : Whether existing data should be imported }
        { --without-messages : Disables output messages }';

    protected $handle = 'collection_trees';

    public function handle()
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-navigation-tree-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('collection_trees');
            },
            message: 'Migrating collection trees...'
        );

        $this->infoMessage('Configured collection trees');

        if ($this->shouldImport('collection trees')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-collections --force --only-collection-trees'),
                message: 'Importing existing collections...'
            );

            $this->infoMessage('Imported existing collection trees');
        }
    }

    public function hasBeenMigrated(): bool
    {
        return config('statamic.eloquent-driver.collection_trees.driver') === 'eloquent';
    }
}
