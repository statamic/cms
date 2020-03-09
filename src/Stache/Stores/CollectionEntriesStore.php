<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use Statamic\Stache\Indexes;
use Symfony\Component\Finder\SplFileInfo;
use Statamic\Entries\GetDateFromPath;
use Statamic\Structures\CollectionStructure;

class CollectionEntriesStore extends ChildStore
{
    public function getFileFilter(SplFileInfo $file) {
        $dir = str_finish($this->directory(), '/');
        $relative = $file->getPathname();

        if (substr($relative, 0, strlen($dir)) == $dir) {
            $relative = substr($relative, strlen($dir));
        }

        // if (! Collection::findByHandle(explode('/', $relative)[0])) {
        //     return false;
        // }

        return $file->getExtension() !== 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        [$collection, $site] = $this->extractAttributesFromPath($path);

        $data = YAML::file($path)->parse($contents);

        if (! $id = array_pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $collectionHandle = $collection;
        $collection = Collection::findByHandle($collectionHandle);

        $entry = Entry::make()
            ->id($id)
            ->collection($collection);

        $slug = pathinfo(Path::clean($path), PATHINFO_FILENAME);

        if ($origin = array_pull($data, 'origin')) {
            $entry->origin($origin);
        }

        $entry
            ->blueprint($data['blueprint'] ?? null)
            ->locale($site)
            ->slug($slug)
            ->initialPath($path)
            ->published(array_pull($data, 'published', true))
            ->data($data);

        // if ($collection->orderable() && ! $collection->getEntryPosition($id)) {
        //     $positionGenerated = true;
        //     $collection->appendEntryPosition($id)->save();
        // }

        if ($collection->dated()) {
            $entry->date((new GetDateFromPath)($path));
        }

        if (isset($idGenerated) || isset($positionGenerated)) {
            $entry->save();
        }

        return $entry;
    }

    protected function extractAttributesFromPath($path)
    {
        $site = Site::default()->handle();
        $collection = pathinfo($path, PATHINFO_DIRNAME);
        $collection = str_after($collection, $this->parent->directory());

        if (Site::hasMultiple()) {
            list($collection, $site) = explode('/', $collection);
        }

        // Support entries within subdirectories at any level.
        if (str_contains($collection, '/')) {
            $collection = str_before($collection, '/');
        }

        return [$collection, $site];
    }

    protected function handleModifiedItem($item)
    {
        $item->taxonomize();
    }

    protected function handleDeletedItem($path, $id)
    {
        [$collection, $site] = $this->extractAttributesFromPath($path);

        $collection = Collection::findByHandle($collection);

        $this->removeEntryFromStructure($collection, $id);
    }

    protected function removeEntryFromStructure($collection, $id)
    {
        if (! $collection->hasStructure()) {
            return;
        }

        $contents = $collection->structureContents();

        $trees = $contents['trees'] ?? [Site::default()->handle() => $contents['tree']];
        unset($contents['tree']);
        $contents['trees'] = $trees;

        $structure = (new CollectionStructure)
            ->collection($collection)
            ->expectsRoot($contents['root'] ?? false)
            ->maxDepth($contents['max_depth'] ?? null);

        $tempStructure = new class extends \Statamic\Structures\Structure {
            public function collections($collections = null) { return collect(); }
        };

        foreach ($trees as $site => $treeContents) {
            $structure->addTree(
                $tempStructure->makeTree($site)->tree($treeContents)->remove($id)
            );
        }

        $collection->structure($structure)->save();
    }

    protected function storeIndexes()
    {
        $indexes = collect([
            'slug',
            'uri',
            'collection',
            'published',
            'title',
            'site' => Indexes\Site::class,
            'origin' => Indexes\Origin::class,
        ]);

        if (! $collection = Collection::findByHandle($this->childKey())) {
            return $indexes->all();
        }

        if ($collection->orderable()) {
            $indexes[] = 'order';
        }

        if ($collection->dated()) {
            $indexes[] = 'date';
        }

        return $indexes->merge(
            $collection->taxonomies()->map->handle()
        )->all();
    }
}
