<?php

namespace Statamic\Data\Services;

use Statamic\API\Str;
use Illuminate\Support\Collection;
use Statamic\Stache\AggregateRepository;
use Statamic\Data\Content\ContentCollection;

class ContentService extends AbstractService
{
    /**
     * Get all content
     *
     * @return ContentCollection
     */
    public function all()
    {
        $entries = app(EntriesService::class)->all();
        $terms = app(TermsService::class)->all();
        $pages = app(PagesService::class)->all();

        return collect_content(
            $entries->merge($terms)->merge($pages)
        );
    }

    /**
     * Get content by ID
     *
     * @param string $id
     * @return mixed|void
     */
    public function id($id)
    {
        // Temporarily do a different check for taxonomy terms.
        // @todo
        if ($term = app(TermsService::class)->id($id)) {
            return $term;
        }

        $ids = $this->getFlattenedIds();

        if (! $ids->has($id)) {
            return;
        }

        return $this->stache->repo($ids->get($id))->getItem($id);
    }

    /**
     * Check if content exists by ID
     *
     * @param string $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->getFlattenedIds()->has($id);
    }

    /**
     * Check if content exists at the given URI
     *
     * @param string $uri
     * @return bool
     */
    public function uriExists($uri)
    {
        // Temporarily do a different check for taxonomy terms.
        // @todo
        if ($term = app(TermsService::class)->uri($uri)) {
            return true;
        }

        return $this->uris()->has(default_locale() . '::' . Str::ensureLeft($uri, '/'));
    }

    /**
     * Get all URIs
     *
     * @return Collection
     */
    public function uris()
    {
        return $this->stache->uris();
    }

    /**
     * Get content by URI
     *
     * @param string $uri
     */
    public function uri($uri)
    {
        // Temporarily do a different check for taxonomy terms.
        // @todo
        if ($term = app(TermsService::class)->uri($uri)) {
            return $term;
        }

        $key = default_locale() . '::' . Str::ensureLeft($uri, '/');

        $id = $this->uris()->get($key);

        return $this->id($id);
    }

    /**
     * Get the default URI given a localized URI
     *
     * @param string $locale  The locale this URI is from
     * @param string $uri     The localized URI
     * @return string|null
     */
    public function defaultUri($locale, $uri)
    {
        // Temporarily do a different check for taxonomy terms.
        // @todo
        if ($defaultTermUri = app(TermsService::class)->getDefaultTermUri($locale, $uri)) {
            return $defaultTermUri;
        }

        $uris = $this->stache->uris();

        // Attempt to get the ID of a localized URI. If an ID is found it means a localized
        // version exists. We'll then need to get the object and pull out the default URI.
        if ($id = $uris->get($locale.'::'.$uri)) {
            $ids = $this->getFlattenedIds();
            return $this->stache->repo($ids->get($id))->getUri($id);
        }

        // Now we'll see if the given URI exists in the default locale. If it does,
        // we'll simply return the URI since it's already in the default locale.
        if ($uris->has(default_locale().'::'.$uri)) {
            return $uri;
        }

        // Womp womp. The provided URI is a dud. It doesn't exist.
        return null;
    }

    /**
     * Get a list of all content IDs and what driver they are stored in.
     *
     * @return Collection
     */
    public function getFlattenedIds()
    {
        $ids = collect();

        $stache = $this->stache;

        $keys = ['pages', 'entries', 'globals'];

        // Organize ids into groups of their repo keys. For AggregateRepos,
        // the keys will be namespaces like so: entries::blog
        foreach ($keys as $key) {
            $repo = $stache->repo($key);
            if ($repo instanceof AggregateRepository) {
                foreach ($repo->repos() as $subrepo) {
                    $tmp = [];
                    foreach ($subrepo->getIds() as $id) {
                        $tmp[$id] = $id;
                    }
                    $ids->put($repo->key().'::'.$subrepo->key(), $tmp);
                }
            } else {
                $ids->put($key, $repo->getIds()->all());
            }
        }

        return $ids->flatMap(function ($ids, $key) {
            $new = [];
            foreach ($ids as $id) {
                $new[$id] = $key;
            }
            return $new;
        });
    }
}
