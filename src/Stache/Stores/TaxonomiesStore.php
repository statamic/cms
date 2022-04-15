<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\YAML;
use Symfony\Component\Finder\SplFileInfo;

class TaxonomiesStore extends BasicStore
{
    protected $storeIndexes = [
        'uri',
    ];

    public function key()
    {
        return 'taxonomies';
    }

    public function getItemKey($item)
    {
        return $item->handle();
    }

    public function getItemFilter(SplFileInfo $file)
    {
        $filename = str_after(Path::tidy($file->getPathName()), $this->directory);

        return $file->getExtension() === 'yaml' && substr_count($filename, '/') === 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::file($path)->parse($contents);

        $sites = array_get($data, 'sites', Site::hasMultiple() ? [] : [Site::default()->handle()]);

        return Taxonomy::make($handle)
            ->title(array_get($data, 'title'))
            ->cascade(array_get($data, 'inject', []))
            ->revisionsEnabled(array_get($data, 'revisions', false))
            ->searchIndex(array_get($data, 'search_index'))
            ->defaultPublishState($this->getDefaultPublishState($data))
            ->sites($sites)
            ->previewTargets($this->normalizePreviewTargets(array_get($data, 'preview_targets', [])));
    }

    protected function getDefaultPublishState($data)
    {
        $value = array_get($data, 'default_status', 'published');

        if (! in_array($value, ['published', 'draft'])) {
            throw new \Exception('Invalid taxonomy default_status value. Must be "published" or "draft".');
        }

        return $value === 'published';
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
