<?php

namespace Statamic\Fieldtypes;

use FilesystemIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Statamic\Support\Str;

class TemplateFolder extends Relationship
{
    protected $component = 'template_folder';
    protected $selectable = false;

    // Intentionally ungated. Both funnels expose only relative template-folder
    // names, and there is no permission concept for templates to authorize against.
    protected function authorizeItemData($id): bool
    {
        return true;
    }

    protected function toItemArray($id, $site = null)
    {
        return ['title' => $id, 'id' => $id];
    }

    public function getIndexItems($request)
    {
        // Intentionally ungated. See authorizeItemData().
        return collect(config('view.paths'))->flatMap(function ($path) {
            return collect(new RecursiveIteratorIterator(
                new RecursiveCallbackFilterIterator(
                    new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS),
                    fn ($file) => $file->isDir() && ! str_starts_with($file->getFilename(), '.') && ! in_array($file->getBaseName(), ['node_modules'])
                ),
                RecursiveIteratorIterator::SELF_FIRST
            ))->map(fn ($file) => Str::of($file->getPathname())
                ->after($path.DIRECTORY_SEPARATOR)
                ->replace('\\', '/')
                ->toString()
            );
        })->map(fn ($folder) => ['id' => $folder, 'title' => $folder])->sort()->values();
    }
}
