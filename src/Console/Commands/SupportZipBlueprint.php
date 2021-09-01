<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Fieldset;
use Statamic\Facades\Path;
use ZipArchive;

class SupportZipBlueprint extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:support:zip-blueprint { blueprint : Path or full handle of a blueprint }';
    protected $description = 'Creates a zip file containing a blueprint and all the fieldsets it uses.';

    public function handle()
    {
        if (! $blueprint = $this->getBlueprint()) {
            return 1;
        }

        $fieldsets = $this->getFieldsets($blueprint);

        if (! $filename = $this->createZip($blueprint, $fieldsets)) {
            return 1;
        }

        $this->info('Zip created successfully.');
        $this->comment("Your zip file awaits: {$filename}");
    }

    protected function createZip($blueprint, $fieldsets)
    {
        $filename = $blueprint->handle().'-blueprint.zip';

        $zip = new ZipArchive();

        if (true !== $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $this->error("Unable to create zip file \"$filename\"");

            return false;
        }

        $zip->addFile($path = $blueprint->path(), Path::makeRelative($path));

        $fieldsets->each(function ($fieldset) use ($zip) {
            $zip->addFile($path = $fieldset->path(), Path::makeRelative($path));
        });

        $zip->close();

        return $filename;
    }

    protected function getBlueprint()
    {
        $handle = $this->argument('blueprint');

        if (Str::endsWith($handle, '.yaml') && ! $handle = $this->getBlueprintHandle($handle)) {
            return null;
        }

        if (! $blueprint = Blueprint::find($handle)) {
            $this->error("Blueprint \"$handle\" not found");

            return null;
        }

        return $blueprint;
    }

    protected function getBlueprintHandle($path)
    {
        $fullPath = Path::makeFull(Path::makeRelative($path));

        if (! Str::startsWith($fullPath, Blueprint::directory())) {
            $this->error("Not a valid blueprint file: \"$path\"");
            $this->comment('Blueprints can be found in '.Path::makeRelative(Blueprint::directory()));

            return null;
        }

        $relativePath = ltrim(Str::after($fullPath, Blueprint::directory()), '/');

        return str_replace('/', '.', Str::before($relativePath, '.yaml'));
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
}
