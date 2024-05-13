<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;
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
        $dir = Str::finish($this->directory, '/');
        $relative = Str::after(Path::tidy($file->getPathname()), $dir);

        return $file->getExtension() === 'yaml' && substr_count($relative, '/') === 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::file($path)->parse($contents);

        $sites = Arr::get($data, 'sites', Site::multiEnabled() ? [] : [Site::default()->handle()]);

        $collection = Collection::make($handle)
            ->title(Arr::get($data, 'title'))
            ->routes(Arr::get($data, 'route'))
            ->requiresSlugs(Arr::get($data, 'slugs', true))
            ->titleFormats(Arr::get($data, 'title_format'))
            ->mount(Arr::get($data, 'mount'))
            ->dated(Arr::get($data, 'date', false))
            ->sites($sites)
            ->template(Arr::get($data, 'template'))
            ->layout(Arr::get($data, 'layout'))
            ->cascade(Arr::get($data, 'inject', []))
            ->searchIndex(Arr::get($data, 'search_index'))
            ->revisionsEnabled(Arr::get($data, 'revisions', false))
            ->defaultPublishState($this->getDefaultPublishState($data))
            ->originBehavior(Arr::get($data, 'origin_behavior', 'select'))
            ->structureContents(Arr::get($data, 'structure'))
            ->sortField(Arr::get($data, 'sort_by'))
            ->sortDirection(Arr::get($data, 'sort_dir'))
            ->taxonomies(Arr::get($data, 'taxonomies'))
            ->propagate(Arr::get($data, 'propagate'))
            ->previewTargets($this->normalizePreviewTargets(Arr::get($data, 'preview_targets', [])))
            ->autosaveInterval(Arr::get($data, 'autosave'));

        if ($dateBehavior = Arr::get($data, 'date_behavior')) {
            $collection
                ->futureDateBehavior($dateBehavior['future'] ?? null)
                ->pastDateBehavior($dateBehavior['past'] ?? null);
        }

        return $collection;
    }

    protected function getDefaultPublishState($data)
    {
        $value = Arr::get($data, 'default_status', 'published');

        if (! in_array($value, ['published', 'draft'])) {
            throw new \Exception('Invalid collection default_status value. Must be "published" or "draft".');
        }

        return $value === 'published';
    }

    public function updateEntryUris($collection, $ids = null)
    {
        $store = Stache::store('entries')->store($collection->handle());
        $this->updateEntriesWithinIndex($store->index('uri'), $ids);
        $this->updateEntriesWithinStore($store, $ids);
    }

    private function updateEntriesWithinStore($store, $ids)
    {
        if (empty($ids)) {
            $ids = $store->paths()->keys();
        }

        $entries = $store->withoutBlinkingEntryUris(fn () => collect($ids)->map(fn ($id) => Entry::find($id))->filter());

        $entries->each(fn ($entry) => $store->cacheItem($entry));
    }

    public function updateEntryOrder($collection, $ids = null)
    {
        $index = Stache::store('entries')
            ->store($collection->handle())
            ->index('order');

        $this->updateEntriesWithinIndex($index, $ids);
    }

    public function updateEntryParent($collection, $ids = null)
    {
        $index = Stache::store('entries')
            ->store($collection->handle())
            ->index('parent');

        $this->updateEntriesWithinIndex($index, $ids);
    }

    private function updateEntriesWithinIndex($index, $ids)
    {
        if (empty($ids)) {
            return $index->update();
        }

        collect($ids)
            ->map(fn ($id) => Entry::find($id))
            ->filter()
            ->each(fn ($entry) => $index->updateItem($entry));
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
                'refresh' => $target['refresh'] ?? true,
            ];
        })->all();
    }
}
