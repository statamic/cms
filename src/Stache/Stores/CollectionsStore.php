<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Collection;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Symfony\Component\Finder\SplFileInfo;

class CollectionsStore extends BasicStore
{
    public function key()
    {
        return 'collections';
    }

    public function getItemKey($item)
    {
        return $item->handle();
    }

    public function getItemFilter(SplFileInfo $file)
    {
        $dir = str_finish($this->directory, '/');
        $relative = str_after(Path::tidy($file->getPathname()), $dir);

        return $file->getExtension() === 'yaml' && substr_count($relative, '/') === 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::file($path)->parse($contents);

        $sites = array_get($data, 'sites', Site::hasMultiple() ? [] : [Site::default()->handle()]);

        $collection = Collection::make($handle)
            ->title(array_get($data, 'title'))
            ->routes(array_get($data, 'route'))
            ->requiresSlugs(array_get($data, 'slugs', true))
            ->titleFormats(array_get($data, 'title_format'))
            ->mount(array_get($data, 'mount'))
            ->dated(array_get($data, 'date', false))
            ->ampable(array_get($data, 'amp', false))
            ->sites($sites)
            ->template(array_get($data, 'template'))
            ->layout(array_get($data, 'layout'))
            ->cascade(array_get($data, 'inject', []))
            ->searchIndex(array_get($data, 'search_index'))
            ->revisionsEnabled(array_get($data, 'revisions', false))
            ->defaultPublishState($this->getDefaultPublishState($data))
            ->originBehavior(array_get($data, 'origin_behavior', 'select'))
            ->structureContents(array_get($data, 'structure'))
            ->sortField(array_get($data, 'sort_by'))
            ->sortDirection(array_get($data, 'sort_dir'))
            ->taxonomies(array_get($data, 'taxonomies'))
            ->propagate(array_get($data, 'propagate'))
            ->previewTargets($this->normalizePreviewTargets(array_get($data, 'preview_targets', [])))
            ->autosaveInterval(array_get($data, 'autosave'));

        if ($dateBehavior = array_get($data, 'date_behavior')) {
            $collection
                ->futureDateBehavior($dateBehavior['future'] ?? null)
                ->pastDateBehavior($dateBehavior['past'] ?? null);
        }

        return $collection;
    }

    protected function getDefaultPublishState($data)
    {
        $value = array_get($data, 'default_status', 'published');

        if (! in_array($value, ['published', 'draft'])) {
            throw new \Exception('Invalid collection default_status value. Must be "published" or "draft".');
        }

        return $value === 'published';
    }

    public function updateEntryUris($collection, $ids = null)
    {
        Stache::store('entries')
            ->store($collection->handle())
            ->index('uri')
            ->update();
    }

    public function updateEntryOrder($collection, $ids = null)
    {
        Stache::store('entries')
            ->store($collection->handle())
            ->index('order')
            ->update();
    }

    public function handleFileChanges()
    {
        if ($this->fileChangesHandled || ! config('statamic.stache.watcher')) {
            return;
        }

        parent::handleFileChanges();

        foreach ($this->modified as $collection) {
            $collection->updateEntryUris();

            if ($collection->orderable()) {
                Stache::store('entries')->store($collection->handle())->index('order')->update();
            }
        }
    }

    private function normalizePreviewTargets($targets)
    {
        return collect($targets)->map(function ($target) {
            return [
                'format' => $target['url'],
                'label' => $target['label'],
            ];
        })->all();
    }
}
