<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;
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
        $filename = Str::after(Path::tidy($file->getPathName()), $this->directory);

        return $file->getExtension() === 'yaml' && substr_count($filename, '/') === 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::file($path)->parse($contents);

        $sites = Arr::get($data, 'sites', Site::multiEnabled() ? [] : [Site::default()->handle()]);

        return Taxonomy::make($handle)
            ->title(Arr::get($data, 'title'))
            ->cascade(Arr::get($data, 'inject', []))
            ->revisionsEnabled(Arr::get($data, 'revisions', false))
            ->searchIndex(Arr::get($data, 'search_index'))
            ->defaultPublishState($this->getDefaultPublishState($data))
            ->sites($sites)
            ->previewTargets($this->normalizePreviewTargets(Arr::get($data, 'preview_targets', [])))
            ->termTemplate(Arr::get($data, 'term_template', null))
            ->template(Arr::get($data, 'template', null))
            ->layout(Arr::get($data, 'layout', null));
    }

    protected function getDefaultPublishState($data)
    {
        $value = Arr::get($data, 'default_status', 'published');

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
                'refresh' => $target['refresh'] ?? true,
            ];
        })->all();
    }
}
