<?php

namespace Statamic\Addons\GetFiles;

use Statamic\API\Folder;
use Statamic\API\Helper;
use Statamic\Extend\Tags;
use Statamic\FileCollection;

class GetFilesTags extends Tags
{
    /**
     * @var \Statamic\FileCollection
     */
    private $files;

    /**
     * The {{ get_files }} tag
     *
     * @return string
     */
    public function index()
    {
        $this->getFiles();

        if ($this->files->count() == 0) {
            return $this->parseNoResults();
        }

        $data = $this->files->toArray();

        return $this->parseLoop($data);
    }

    /**
     * Get all the files from the selected folders at the appropriate depth
     *
     * @return \Illuminate\Support\Collection
     */
    private function getFiles()
    {
        $folders = $this->get(['in', 'from']);
        $depth = $this->getInt('depth', 1);

        $this->files = new FileCollection;

        foreach (Helper::explodeOptions($folders) as $folder) {
            $folder_files = [];

            if ($depth > 1) {
                // If the depth is greater than a single level, we'll get the files recursively which
                // will get all depths, then we'll need to reject anything deeper than specified.
                foreach (Folder::getFilesRecursively($folder) as $path) {
                    $slashes = substr_count($path, '/') - substr_count($folder, '/');

                    if ($slashes <= $depth) {
                        $folder_files[] = $path;
                    }
                }
            } else {
                // Single level of depth? Simple.
                $folder_files = Folder::getFiles($folder);
            }

            $this->files = $this->files->merge($folder_files);
        }

        // Continue on our way with the other filters
        $this->filter();
    }

    /**
     * Filter the files
     */
    private function filter()
    {
        $this->filterNotIn();
        $this->filterExtension();
        $this->filterSize();
        $this->filterRegex();
        $this->filterDate();

        $this->sort();
        $this->limit();
    }

    /**
     * Filter out files from a requested folder
     */
    private function filterNotIn()
    {
        if ($not_in = $this->get('not_in')) {
            $regex = '#^(' . $not_in . ')#';

            $this->files = $this->files->reject(function($path) use ($regex) {
                return preg_match($regex, $path);
            });
        }
    }

    /**
     * Filter files by file size
     */
    private function filterSize()
    {
        if ($size = $this->get('file_size')) {
            $this->files = $this->files->filterBySize($size);
        }
    }

    /**
     * Filter files by extension(s)
     */
    private function filterExtension()
    {
        if ($extensions = $this->get(['extension', 'ext'])) {
            $extensions = Helper::explodeOptions($extensions);

            $this->files = $this->files->filterByExtension($extensions);
        }
    }

    private function filterRegex()
    {
        if ($include = $this->get(['include', 'match'])) {
            $this->files = $this->files->filterByRegex($include);
        }

        if ($exclude = $this->get('exclude')) {
            $this->files = $this->files->rejectByRegex($exclude);
        }
    }

    private function filterDate()
    {
        if ($date = $this->get('file_date')) {
            $this->files = $this->files->filterByDate($date);
        }
    }

    private function limit()
    {
        $limit = $this->getInt('limit');
        $limit = ($limit == 0) ? $this->files->count() : $limit;
        $offset = $this->getInt('offset');

        $this->files = $this->files->splice($offset, $limit);
    }

    private function sort()
    {
        if ($sort = $this->get('sort')) {
            $this->files = $this->files->multisort($sort);
        }
    }
}
