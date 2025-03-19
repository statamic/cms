<?php

namespace Statamic\UpdateScripts;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\GlobalVariables;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;

class UpdateGlobalVariables extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        // This update script deals with reading & writing YAML files from the filesystem.
        // There's an equivalent update script for the Eloquent driver that deals with
        // reading & writing to the database.
        if (config('statamic.eloquent-driver.global_set_variables.driver') === 'eloquent') {
            return;
        }

        match (Site::multiEnabled()) {
            true => $this->buildSitesArray(),
            false => $this->migrateFileStructure(),
        };
    }

    /**
     * Adds the `sites` array to the global set based on the global variables that exist.
     * It also removes the `origin` key from global variables.
     */
    private function buildSitesArray(): void
    {
        GlobalSet::all()->each(function ($globalSet) {
            $variables = GlobalVariables::whereSet($globalSet->handle());

            $sites = $variables->mapWithKeys(function ($variable) {
                $contents = YAML::file($variable->path())->parse();
                $origin = Arr::get($contents, 'origin');

                return [$variable->locale() => $origin];
            });

            $globalSet->sites($sites)->save();

            $variables->each(function ($variable) {
                $data = YAML::file($variable->path())->parse();

                File::put($variable->path(), YAML::dump(Arr::except($data, 'origin')));
            });
        });
    }

    /**
     * Migrates global variables in a single-site install to the new directory structure.
     */
    private function migrateFileStructure(): void
    {
        GlobalSet::all()->each(function ($globalSet) {
            $variablesDirectory = Stache::store('global-variables')->directory().Site::default()->handle().'/';
            $variablesPath = $variablesDirectory.$globalSet->handle().'.yaml';

            if (File::exists($variablesPath)) {
                return;
            }

            $contents = YAML::file($globalSet->path())->parse();
            $data = Arr::get($contents, 'data', []);

            File::ensureDirectoryExists($variablesDirectory);
            File::put($variablesPath, YAML::dump($data));

            $globalSet->save();
        });
    }
}
