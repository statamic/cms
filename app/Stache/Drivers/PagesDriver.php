<?php

namespace Statamic\Stache\Drivers;

use Statamic\API\Config;
use Statamic\API\Path;
use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\Page;
use Statamic\API\YAML;
use Statamic\Stache\Repository;
use Statamic\Contracts\Data\Content\StatusParser;

class PagesDriver extends AbstractDriver
{
    protected $localizable = true;
    protected $routable = true;

    public function getFilesystemRoot()
    {
        return 'pages';
    }

    public function createItem($path, $contents)
    {
        $data = YAML::parse($contents);

        // The publish state can be located in the yaml. Otherwise, infer it from the path.
        $published = array_get($data, 'published', app(StatusParser::class)->pagePublished($path));

        return Page::create(URL::buildFromPath($path))
            ->path($path)
            ->with($data)
            ->published($published)
            ->order(app('Statamic\Contracts\Data\Content\OrderParser')->getPageOrder($path))
            ->get();
    }

    public function isMatchingFile($file)
    {
        if ($file['type'] === 'dir') {
            return false;
        }

        return Str::endsWith($file['filename'], 'index');
    }

    public function getLocaleFromPath($path)
    {
        $filename = pathinfo($path)['filename'];

        if (! Str::contains($filename, '.')) {
            return default_locale();
        }

        return explode('.', $filename)[0];
    }

    public function getLocalizedUri($locale, $data, $path)
    {
        $uri = '/';

        // Homepage? The URI will always just be a single slash.
        $pi = pathinfo($path);
        if ($pi['dirname'] == 'pages' && $pi['filename'] === "$locale.index") {
            return '/';
        }

        // Get the slug. This will be in the data if it's localized, otherwise
        // it'll come from the default locale which is just the folder.
        if (! $slug = array_get($data, 'slug')) {
            $slug = Path::folder(Path::clean($path));
        }

        // More than 2 slashes in the path means it's nested inside a parent folder.
        // We'll look up the parent's URL which should already be in the Stache.
        if (substr_count($path, '/') > 2) {
            $parent_path = Path::popLastSegment(Path::popLastSegment($path)) . '/index.' . Config::get('system.default_extension');
            $repo = $this->stache->repo('pages');
            $parent = $repo->getItem($repo->getIdByPath($parent_path));
            $uri = $parent->in($locale)->uri();
        }

        return URL::assemble($uri, $slug);
    }

    public function toPersistentArray($repo)
    {
        $pages = $repo->getItems()->map(function ($page) {
            return $page->shrinkWrap();
        })->all();

        return [
            'meta' => [
                'paths' => $repo->getPathsForAllLocales()->toArray(),
                'uris' => $repo->getUrisForAllLocales()->toArray(),
            ],
            'items' => ['data' => $pages]
        ];
    }

    /**
     * @inheritdoc
     */
    public function load($collection)
    {
        return $collection->map(function ($item, $id) {
            $attr = $item['attributes'];

            // Get the data for the default locale. Remove the ID since
            // we already have it and will be setting it separately.
            $data = $item['data'][default_locale()];
            unset($data['id']);

            $page = Page::create($attr['uri'])
                ->id($id)
                ->with($data)
                ->path($attr['path'])
                ->published($attr['published'])
                ->order(array_get($attr, 'order'))
                ->get();

            // If the page has additional locale data, add them.
            if (count($item['data']) > 1) {
                foreach ($item['data'] as $locale => $data) {
                    $page->dataForLocale($locale, $data);
                }

                $page->syncOriginal();
            }

            return $page;
        });
    }
}
