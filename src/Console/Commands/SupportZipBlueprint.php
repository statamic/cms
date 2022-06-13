<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Fieldset;
use ZipArchive;

class SupportZipBlueprint extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:support:zip-blueprint { blueprint : The blueprint. (e.g. user or collections/blog/post) }';
    protected $description = 'Creates a zip file containing a blueprint and all the fieldsets it uses.';

    public function handle()
    {
        if (! $blueprint = $this->getBlueprint()) {
            return 1;
        }

        if (! $filename = $this->createZip($blueprint)) {
            return 1;
        }

        $this->info('Zip created successfully.');
        $this->comment("Your zip file awaits: {$filename}");
    }

    protected function createZip($blueprint)
    {
        $filename = $blueprint->handle().'-blueprint.zip';

        $zip = new ZipArchive();

        if (true !== $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $this->error("Unable to create zip file \"$filename\"");

            return false;
        }

        $zip->addFile($path = $blueprint->path(), $this->relativePath($path));

        $this->getFieldsets($blueprint)->each(function ($fieldset) use ($zip) {
            $zip->addFile($path = $fieldset->path(), $this->relativePath($path));
        });

        $zip->close();

        return $filename;
    }

    protected function getBlueprint()
    {
        $handle = $this->argument('blueprint');

        if (! $blueprint = Blueprint::find($handle)) {
            $this->error("Blueprint \"$handle\" not found");

            return null;
        }

        return $blueprint;
    }

    protected function getFieldsets($blueprint)
    {
        return $this->getFieldsetHandles($blueprint)->map(function ($handle) {
            return Fieldset::find($handle);
        });
    }

    protected function getFieldsetHandles($blueprint)
    {
        return $blueprint->sections()->map->fields()->flatMap->items()->map(function ($field) {
            if (isset($field['import'])) {
                return $field['import'];
            } elseif (is_string($field['field'])) {
                return Str::before($field['field'], '.');
            }
        })->filter()->unique()->values();
    }

    protected function relativePath($path)
    {
        return Str::after($path, resource_path());
    }
}
