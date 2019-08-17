<?php

namespace Statamic\Stache\Stores;

use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\API\Entry;
use Statamic\API\Collection;
use Statamic\Stache\Indexes;
use Symfony\Component\Finder\SplFileInfo;

class CollectionEntriesStore extends ChildStore
{
    protected $storeIndexes = [
        'slug',
        'site' => Indexes\Site::class,
    ];

    public function filter(SplFileInfo $file)
    {
        $dir = str_finish($this->directory, '/');
        $relative = $file->getPathname();

        if (substr($relative, 0, strlen($dir)) == $dir) {
            $relative = substr($relative, strlen($dir));
        }

        // if (! Collection::findByHandle(explode('/', $relative)[0])) {
        //     return false;
        // }

        return $file->getExtension() !== 'yaml' && substr_count($relative, '/') > 0;
    }

    public function makeItemFromFile($path, $contents)
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

        $data = YAML::parse($contents);

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

        // if ($origin = Arr::pull($data, 'origin')) {
        //     $this->localizationQueue[] = [
        //         'origin' => $origin,
        //         'localization' => $entry,
        //     ];
        // }

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

        // if ($collection->dated()) { // TODO
        if (true) {
            $entry->date(app('Statamic\Contracts\Data\Content\OrderParser')->getEntryOrder($path));
        }

        if (isset($idGenerated) || isset($positionGenerated)) {
            $entry->save();
        }

        // $this->updatedEntries[] = $entry;

        return $entry;
    }

    public function save($entry)
    {
        File::put($path = $entry->path(), $entry->fileContents());

        if (($initial = $entry->initialPath()) && $path !== $initial) {
            File::delete($entry->initialPath());
        }

        $this->forgetItem($entry->id());

        $this->updateItemIndexes($entry);
    }
}