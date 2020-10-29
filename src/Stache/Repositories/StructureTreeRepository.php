<?php

namespace Statamic\Stache\Repositories;

use Statamic\Contracts\Structures\Tree;
use Statamic\Contracts\Structures\TreeRepository;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Structures\CollectionStructureTree;
use Statamic\Structures\NavTree;
use Statamic\Support\Str;

class StructureTreeRepository implements TreeRepository
{
    public function __construct()
    {
        $this->directory = Str::removeRight(base_path('content/structures'), '/');
    }

    public function find($tree): ?Tree
    {
        $filename = $tree.'.yaml';
        $path = $this->directory.'/'.$filename;

        if (! File::exists($path)) {
            return null;
        }

        return $this->makeTreeFromFile($path);
    }

    private function makeTreeFromFile($path)
    {
        return $this
            ->newTreeClassByPath($path)
            ->tree(YAML::file($path)->parse()['tree'])
            ->syncOriginal();
    }

    private function newTreeClassByPath($path)
    {
        [$type] = explode('/', Str::after($path, $this->directory.'/'));

        if ($type == 'collections') {
            return new CollectionStructureTree;
        } elseif ($type == 'navigation') {
            return new NavTree;
        }

        throw new \Exception("Unknown structure tree type [$type]");
    }

    public function save(Tree $tree)
    {
        $tree->writeFile();

        return true;
    }

    public static function bindings()
    {
        return [];
    }
}
