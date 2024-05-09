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

    protected function toItemArray($id, $site = null)
    {
        return ['title' => $id, 'id' => $id];
    }

    public function getIndexItems($request)
    {
        return collect(config('view.paths'))
            ->flatMap(function ($path) {
                $directories = collect();
                $filter = ['.git', 'node_modules'];

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveCallbackFilterIterator(
                        new RecursiveDirectoryIterator(
                            $path,
                            FilesystemIterator::SKIP_DOTS

                        ),
                        function ($fileInfo, $key, $iterator) use ($filter) {
                            return ! $iterator->isLink() && $fileInfo->isDir() && ! in_array($fileInfo->getBaseName(), $filter);
                        }
                    ),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($iterator as $file) {
                    $directories->push(Str::replaceFirst($path.DIRECTORY_SEPARATOR, '', $file->getPathname()));
                }

                return $directories->filter()->values();
            })
            ->map(fn ($folder) => ['id' => $folder, 'title' => $folder])
            ->values();
    }
}
