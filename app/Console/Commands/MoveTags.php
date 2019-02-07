<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MoveTags extends Command
{
    protected $signature = 'temp:move:tags';

    public function handle()
    {
        // Move files
        collect(File::allFiles(base_path('../cms/bundles')))
            ->filter(function ($file) {
                return preg_match('/Tags.php/', $file->getRelativePathname());
            })
            ->map
            ->getRelativePathname()
            ->each(function ($old) {
                $filename = collect(explode('/', $old))->last();
                $filename = str_replace('Tags.php', '.php', $filename);
                File::move(base_path("../cms/bundles/{$old}"), base_path("../cms/app/Tags/{$filename}"));
            });

        // Fix class, namespace, and base class
        collect(File::allFiles(base_path('../cms/app/Tags')))
            ->map
            ->getRelativePathname()
            ->each(function ($file) {
                $path = base_path("../cms/app/Tags/{$file}");
                $contents = File::get($path);
                $contents = preg_replace('/namespace.*;/', 'namespace Statamic\Tags;', $contents);
                $contents = str_replace('Tags extends', ' extends', $contents);
                $contents = str_replace('use Statamic\Extend\Tags;', 'use Statamic\Tags\Tag;', $contents);
                $contents = str_replace('extends Tags', 'extends Tag', $contents);
                File::put($path, $contents);
            });
    }
}
