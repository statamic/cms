<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\File;

class UseClassBasedStatamicUniqueRules extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.0');
    }

    public function update()
    {
        collect()
            ->merge(File::getFilesRecursively(resource_path('blueprints')))
            ->merge(File::getFilesRecursively(resource_path('fieldsets')))
            ->filter(fn ($path) => in_array(pathinfo($path)['extension'], ['yaml', 'yml']))
            ->each(fn ($path) => $this->updateStringBasedRules($path));
    }

    private function updateStringBasedRules($path)
    {
        $contents = File::get($path);

        $contents = str_replace(
            "'unique_entry_value:{collection},{id},{site}'",
            "'new \\Statamic\\Rules\\UniqueEntryValue({collection}, {id}, {site})'",
            $contents
        );

        $contents = str_replace(
            "'unique_term_value:{taxonomy},{id},{site}'",
            "'new \\Statamic\\Rules\\UniqueTermValue({taxonomy}, {id}, {site})'",
            $contents
        );

        $contents = str_replace(
            "'unique_user_value:{id}'",
            "'new \\Statamic\\Rules\\UniqueUserValue({id})'",
            $contents
        );

        // If they were using the documented (but optional) `column` parameter on the `unique_user_value` rule
        $contents = preg_replace(
            "/'unique_user_value:\{id\}(,)([^']+)'/",
            "'new \\Statamic\\Rules\\UniqueUserValue({id}, \"$2\")'",
            $contents
        );

        File::put($path, $contents);
    }
}
