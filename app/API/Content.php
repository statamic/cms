<?php

namespace Statamic\API;

use Statamic\Data\Services\ContentService;
use Statamic\Data\Services\PageStructureService;

class Content
{
    /**
     * Get all content
     *
     * @return \Statamic\Data\Content\ContentCollection
     */
    public static function all()
    {
        return app(ContentService::class)->all();
    }

    /**
     * Get content by ID
     *
     * @param string $id
     * @return mixed
     */
    public static function find($id)
    {
        return app(ContentService::class)->id($id);
    }

    /**
     * Get the raw Content object for a URI
     *
     * @param string      $uri       The URI to look for.
     * @return \Statamic\Contracts\Data\Content\Content
     */
    public static function whereUri($uri)
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
    public static function exists($id)
    {
        return app(ContentService::class)->exists($id);
    }

    /**
     * Check if content exists by URI
     *
     * @param string $uri
     * @return bool
     */
    public static function uriExists($uri)
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
    public static function tree(
        $uri = null,
        $depth = null,
        $entries = null,
        $drafts = null,
        $exclude = null,
        $locale = null
    ) {
        return app(PageStructureService::class)->tree($uri, $depth, $entries, $drafts, $exclude, $locale);
    }

    /**
     * Get the raw content object by ID
     *
     * @param string      $uuid
     * @return \Statamic\Contracts\Data\Content\Content
     * @deprecated since 2.1
     */
    public static function uuidRaw($uuid)
    {
        \Log::notice('Content::uuidRaw() is deprecated. Use Content::find()');

        return self::find($uuid);
    }

    /**
     * Get the content from a UUID
     *
     * @param string      $uuid
     * @return array
     * @deprecated since 2.1
     */
    public static function uuid($uuid)
    {
        \Log::notice('Content::uuid() is deprecated. Use Content::find()->toArray()');

        return self::find($uuid)->toArray();
    }

    /**
     * Get the raw Page object for a single URL
     *
     * @param string $uri URI to find
     * @return \Statamic\Contracts\Data\Pages\Page
     * @deprecated since 2.1
     */
    public static function pageRaw($uri)
    {
        \Log::notice('Content::pageRaw() is deprecated. Use Page::whereUri()');

        return Page::whereUri($uri);
    }

    /**
     * Get content for a single URL
     *
     * @param string $url URL to find
     * @return array
     * @deprecated since 2.1
     */
    public static function page($url)
    {
        \Log::notice('Content::page() is deprecated. Use Page::whereUri()->toArray()');

        return Page::whereUri($url)->toArray();
    }

    /**
     * Get the raw Entry object for a slug
     *
     * @param string      $slug       Slug to find
     * @param string      $collection Collection to look inside
     * @return \Statamic\Contracts\Data\Entries\Entry
     * @deprecated since 2.1
     */
    public static function entryRaw($slug, $collection)
    {
        \Log::notice('Content::entryRaw() is deprecated. Use Entry::whereSlug()');

        return Entry::whereSlug($slug, $collection);
    }

    /**
     * Get the content for an entry
     *
     * @param string      $collection
     * @param string      $slug
     * @return mixed
     */
    public static function entry($collection, $slug)
    {
        \Log::notice('Content::entryRaw() is deprecated. Use Entry::whereSlug()->toArray()');

        return Entry::whereSlug($slug, $collection)->toArray();
    }

    /**
     * Get the raw Taxonomy object for a slug
     *
     * @param string      $slug   Slug to find
     * @param string      $taxonomy  Taxonomy to look inside
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     * @deprecated since 2.1
     */
    public static function taxonomyTermRaw($slug, $taxonomy)
    {
        \Log::notice('Content::taxonomyTermRaw() is deprecated. Use Term::whereSlug()');

        return Term::whereSlug($slug, $taxonomy);
    }

    /**
     * Get the content for a taxonomy
     *
     * @param string      $taxonomy
     * @param string      $slug
     * @param string|null $locale
     * @return mixed
     * @deprecated since 2.1
     */
    public static function taxonomyTerm($taxonomy, $slug, $locale = null)
    {
        \Log::notice('Content::taxonomyTermRaw() is deprecated. Use Term::whereSlug()->toArray()');

        return Term::whereSlug($slug, $taxonomy)->toArray();
    }

    /**
     * Get the raw Content object for a URI
     *
     * @param string      $uri       The URI to look for.
     * @return \Statamic\Contracts\Data\Content\Content
     * @deprecated since 2.1
     */
    public static function getRaw($uri)
    {
        \Log::notice('Content::getRaw() is deprecated. Use Content::whereUri()');

        return self::whereUri($uri);
    }

    /**
     * Get the content from a URL
     *
     * @param string|array $uri
     * @return mixed
     * @deprecated since 2.1
     */
    public static function get($uri)
    {
        \Log::notice('Content::getRaw() is deprecated. Use Content::whereUri()->toArray()');

        return self::whereUri($uri)->toArray();
    }

    /**
     * Get the raw Entry object by URL
     *
     * @param string      $url       The URL to look for
     * @return \Statamic\Contracts\Data\Entries\Entry
     * @deprecated since 2.1
     */
    public static function entryByUrlRaw($url)
    {
        \Log::notice('Content::entryByUrlRaw() is deprecated. Use Entry::whereUri()');

        return Entry::whereUri($url);
    }

    /**
     * Get an entry's content by URL
     *
     * @param string      $url
     * @return \Statamic\Contracts\Data\Entries\Entry
     * @deprecated since 2.1
     */
    public static function entryByUrl($url)
    {
        \Log::notice('Content::entryByUrlRaw() is deprecated. Use Entry::whereUri()->toArray()');

        return Entry::whereUri($url);
    }

    /**
     * Get the raw Taxonomy term object by URL
     *
     * @param string      $url
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     * @deprecated since 2.1
     */
    public static function taxonomyTermByUrlRaw($url)
    {
        \Log::notice('Content::taxonomyTermByUrlRaw() is deprecated. Use Term::whereUri()');

        return Term::whereUri($url);
    }

    /**
     * Get a taxonomy term's content by URL
     *
     * @param string      $url
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     * @deprecated since 2.1
     */
    public static function taxonomyTermByUrl($url)
    {
        \Log::notice('Content::taxonomyTermByUrlRaw() is deprecated. Use Term::whereUri()->toArray()');

        return Term::whereUri($url)->toArray();
    }

    /**
     * Get all entries
     *
     * @param string|null $collection Collection to get entries from
     * @return \Statamic\Data\Entries\EntryCollection
     * @deprecated since 2.1
     */
    public static function entries($collection = null)
    {
        if ($collection) {
            \Log::notice('Content::entries() is deprecated. Use Entry::whereCollection()');

            return Entry::whereCollection($collection);
        }

        \Log::notice('Content::entries() is deprecated. Use Entry::all()');

        return Entry::all();
    }

    /**
     * Get all pages
     *
     * @param array|null  $urls
     * @return \Statamic\Data\Pages\PageCollection
     * @deprecated since 2.1
     */
    public static function pages($urls = null)
    {
        if ($urls) {
            \Log::notice('Content::pages() is deprecated. Use Page::whereUriIn()');

            return Page::whereUriIn($urls);
        }

        \Log::notice('Content::pages() is deprecated. Use Page::all()');

        return Page::all();
    }

    /**
     * Get all taxonomies
     *
     * @param string|null $taxonomy Taxonomy to get terms from
     * @return \Statamic\Data\Taxonomies\TermCollection
     * @deprecated since 2.1
     */
    public static function taxonomyTerms($taxonomy = null)
    {
        if ($taxonomy) {
            \Log::notice('Content::taxonomyTerms() is deprecated. Use Term::whereTaxonomy()');

            return Term::whereTaxonomy($taxonomy);
        }

        \Log::notice('Content::taxonomyTerms() is deprecated. Use Term::all()');

        return Term::all();
    }

    /**
     * Get all globals
     *
     * @param array|null  $slug
     * @return \Statamic\Data\Globals\GlobalCollection
     * @deprecated since 2.1
     */
    public static function globals($slug = null)
    {
        if ($slug) {
            \Log::notice('Content::globals() is deprecated. Use GlobalSet::whereHandle()');

            return GlobalSet::whereHandle($slug);
        }

        \Log::notice('Content::globals() is deprecated. Use GlobalSet::all()');

        return GlobalSet::all();
    }

    /**
     * Get a global set
     *
     * @param      $slug
     * @return \Statamic\Contracts\Data\Globals\GlobalSet
     * @deprecated since 2.1
     */
    public static function globalSet($slug)
    {
        \Log::notice('Content::globals() is deprecated. Use GlobalSet::whereHandle()');

        return GlobalSet::whereHandle($slug);
    }

    /**
     * Get all collections
     *
     * @return \Statamic\Contracts\Data\Entries\Collection[]
     * @deprecated since 2.1
     */
    public static function collections()
    {
        \Log::notice('Content::collections() is deprecated. Use Collection::all()');

        return Collection::all()->all(); // double all because we want an array
    }

    /**
     * @param string      $collection
     * @return \Statamic\Contracts\Data\Entries\Collection
     * @deprecated since 2.1
     */
    public static function collection($collection)
    {
        \Log::notice('Content::collection() is deprecated. Use Collection::whereHandle()');

        return Collection::whereHandle($collection);
    }

    /**
     * Get the names of all the collections
     *
     * @return array
     * @deprecated since 2.1
     */
    public static function collectionNames()
    {
        \Log::notice('Content::collectionNames() is deprecated. Use Collection::handles()');

        return Collection::handles();
    }

    /**
     * Check if a collection exists
     *
     * @param string $collection
     * @return bool
     * @deprecated since 2.1
     */
    public static function collectionExists($collection)
    {
        \Log::notice('Content::collectionExists() is deprecated. Use Collection::handleExists()');

        return Collection::handleExists($collection);
    }

    /**
     * Check if a entry exists
     *
     * @param string      $slug
     * @param string      $collection
     * @return bool
     * @deprecated since 2.1
     */
    public static function entryExists($slug, $collection)
    {
        \Log::notice('Content::entryExists() is deprecated. Use Entry::slugExists()');

        return Entry::slugExists($slug, $collection);
    }

    /**
     * Check if a page exists
     *
     * @param string      $url
     * @return bool
     * @deprecated since 2.1
     */
    public static function pageExists($url)
    {
        \Log::notice('Content::pageExists() is deprecated. Use Page::uriExists()');

        return Page::uriExists($url);
    }

    /**
     * Check if a taxonomy term exists
     *
     * @param string      $slug
     * @param string      $taxonomy
     * @return bool
     * @deprecated since 2.1
     */
    public static function taxonomyTermExists($slug, $taxonomy)
    {
        \Log::notice('Content::taxonomyTermExists() is deprecated. Use Term::slugExists()');

        return Term::slugExists($slug, $taxonomy);
    }

    /**
     * Get all taxonomies
     *
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy[]
     * @deprecated since 2.1
     */
    public static function taxonomies()
    {
        \Log::notice('Content::taxonomies() is deprecated. Use Taxonomy::all()');

        return Taxonomy::all()->all(); // double all because we want an array
    }

    /**
     * Get a taxonomy
     *
     * @param string      $taxonomy
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     * @deprecated since 2.1
     */
    public static function taxonomy($taxonomy)
    {
        \Log::notice('Content::taxonomy() is deprecated. Use Taxonomy::whereHandle()');

        return Taxonomy::whereHandle($taxonomy);
    }

    /**
     * Check if a taxonomy exists
     *
     * @param string $taxonomy
     * @return bool
     * @deprecated since 2.1
     */
    public static function taxonomyExists($taxonomy)
    {
        \Log::notice('Content::taxonomyExists() is deprecated. Use Taxonomy::handleExists()');

        return Taxonomy::handleExists($taxonomy);
    }

    /**
     * @return array
     * @deprecated since 2.1
     */
    public static function taxonomyNames()
    {
        \Log::notice('Content::taxonomyNames() is deprecated. Use Taxonomy::handles()');

        return Taxonomy::handles();
    }

    /**
     * @return \Statamic\Contracts\Data\Pages\PageFolder[]
     * @deprecated since 2.1
     */
    public static function pageFolders()
    {
        \Log::notice('Content::pageFolders() is deprecated. Use PageFolder::all()');

        return PageFolder::all()->all(); // double all because we want an array
    }

    /**
     * @param string $path
     * @return \Statamic\Contracts\Data\Pages\PageFolder
     * @deprecated since 2.1
     */
    public static function pageFolder($path)
    {
        \Log::notice('Content::pageFolder() is deprecated. Use PageFolder::whereHandle()');

        return PageFolder::whereHandle($path);
    }
}
