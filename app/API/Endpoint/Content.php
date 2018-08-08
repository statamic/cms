<?php

namespace Statamic\API\Endpoint;

use Statamic\API\Page;
use Statamic\API\Term;
use Statamic\API\Entry;
use Statamic\API\Helper;
use Statamic\API\GlobalSet;
use Statamic\API\Collection;
use Statamic\API\PageFolder;
use Statamic\Data\Services\ContentService;
use Statamic\Data\Services\PageStructureService;

class Content
{
    /**
     * Get all content
     *
     * @return \Statamic\Data\Content\ContentCollection
     */
    public function all()
    {
        return app(ContentService::class)->all();
    }

    /**
     * Get content by ID
     *
     * @param string $id
     * @return mixed
     */
    public function find($id)
    {
        return app(ContentService::class)->id($id);
    }

    /**
     * Get the raw Content object for a URI
     *
     * @param string      $uri       The URI to look for.
     * @return \Statamic\Contracts\Data\Content\Content
     */
    public function whereUri($uri)
    {
        if ($uri === null) {
            return null;
        }

        $is_array   = is_array($uri);
        $uris       = Helper::ensureArray($uri);
        $collection = collect_content();

        foreach ($uris as $uri) {
            $collection->push(app(ContentService::class)->uri($uri));
        }

        return ($is_array) ? $collection : $collection->first();
    }

    /**
     * Check if content exists by ID
     *
     * @param string $id
     * @return bool
     */
    public function exists($id)
    {
        return app(ContentService::class)->exists($id);
    }

    /**
     * Check if content exists by URI
     *
     * @param string $uri
     * @return bool
     */
    public function uriExists($uri)
    {
        return app(ContentService::class)->uriExists($uri);
    }

    /**
     * Get the content tree
     *
     * @param string       $uri
     * @param int          $depth
     * @param bool         $entries
     * @param bool         $drafts
     * @param bool         $exclude
     * @param string|null  $locale
     * @return array
     */
    public function tree(
        $uri = null,
        $depth = null,
        $entries = null,
        $drafts = null,
        $exclude = null,
        $locale = null
    ) {
        return app(PageStructureService::class)->tree($uri, $depth, $entries, $drafts, $exclude, $locale);
    }
}
