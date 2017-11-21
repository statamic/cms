<?php

namespace Statamic\Stache\Staches;

use Statamic\API\File;
use Statamic\API\Folder;
use Statamic\API\Term;
use Statamic\API\Config;
use Statamic\API\Taxonomy;
use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Statamic\API\YAML;
use Statamic\Contracts\Data\Taxonomies\Term as TermContract;

class TaxonomyStache
{
    /**
     * The site's default locale.
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     * Collection of terms and the data IDs that are associated with them.
     *
     * @var Collection
     */
    protected $associations;

    /**
     * Collection of term URIs grouped by locale.
     *
     * @var Collection
     */
    protected $uris;

    /**
     * Collection of titles/values that were used to reference terms within data.
     *
     * @var Collection
     */
    protected $titles;

    /**
     * Cached terms.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $terms;

    /**
     * @param string|null $locale  The default locale
     */
    public function __construct($locale = null)
    {
        $this->associations = collect();
        $this->uris = collect();
        $this->terms = collect();
        $this->titles = collect();

        $this->defaultLocale = $locale ?: default_locale();
    }

    /**
     * Sync data associations.
     *
     * @param string $id        The data ID
     * @param string $taxonomy  The taxonomy slug
     * @param array  $terms     The terms remaining in the data
     */
    public function syncAssociations($id, $taxonomy, $terms)
    {
        $terms = collect($terms);

        $normalizedTerms = $terms->map(function ($term) {
            return Term::normalizeSlug($term);
        });

        // First, we should add all the associations and URIs.
        foreach ($terms as $term) {
            $this->associateDataWithTerm($taxonomy, $term, $id);
        }

        // Then we need to iterate over all existing associations and remove any that no longer apply.
        foreach ($this->associations as $key => $associations) {
            list($tax, $term) = explode('/', $key);

            // If the iterated term is in another taxonomy, we don't care about it.
            if ($tax !== $taxonomy) {
                continue;
            }

            // If the currently iterated term is present in the data, we don't need to try to remove it.
            if ($normalizedTerms->contains($term)) {
                continue;
            }

            $associations = $associations->diff(collect([$id]))->values();

            $this->insertAssociations($key, $associations);
        }
    }

    private function insertAssociations($key, $associations)
    {
        list($taxonomy, $term) = explode('/', $key);

        if ($associations->isEmpty()) {
            // If there are no associations left, we want to remove the term.
            $this->removeTerm($taxonomy, $term);
        } else {
            // Otherwise just update the association with the leftovers.
            $this->associations->put($key, $associations);
        }
    }

    /**
     * Associate data with a term.
     *
     * @param string $taxonomy  The taxonomy handle
     * @param string $term      The term slug
     * @param string $id        The data ID
     */
    public function associateDataWithTerm($taxonomy, $term, $id)
    {
        $key = $this->getTermKey($taxonomy, $term);

        $this->addAssociation($key, $id);

        $this->addTitle($key, $term);

        $this->addUris($taxonomy, $term);
    }

    /**
     * Remove references to associated data
     *
     * @param string $id  The data ID
     */
    public function removeData($id)
    {
        foreach ($this->associations as $key => $associations) {
            $this->insertAssociations(
                $key,
                $associations->diff(collect([$id]))->values()
            );
        }
    }

    private function addAssociation($key, $id)
    {
        if (! $this->associations->has($key)) {
            $this->associations->put($key, collect());
        }

        $termAssociations = $this->associations->get($key);

        if (! $termAssociations->contains($id)) {
            $termAssociations->push($id);
        }
    }

    /**
     * Get all the data associations.
     *
     * @param string|TermContract|null $term  When provided, associations for a specific term will be returned.
     * @return Collection
     */
    public function getAssociations($term = null)
    {
        if (! $term) {
            return $this->associations;
        }

        if ($term instanceof TermContract) {
            $term = $term->id();
        }

        return $this->associations->get($term, collect());
    }

    /**
     * Get the term titles.
     *
     * @return Collection
     */
    public function getTitles()
    {
        return $this->titles;
    }

    public function getTitle($key)
    {
        return $this->titles->get($key);
    }

    /**
     * Add a title for a given term
     *
     * @param string $key    Term key
     * @param string $title  Term title
     * @return void
     */
    private function addTitle($key, $title)
    {
        // If a title has already been provided, don't overwrite with a new one.
        if ($this->titles->has($key)) {
            return;
        }

        $this->titles->put($key, $title);
    }

    /**
     * Add a term to this request's cache.
     *
     * @param string $taxonomy    The taxonomy handle
     * @param string $slug        The term slug
     * @param TermContract $term  The term object
     * @return void
     */
    public function addTerm($taxonomy, $slug, TermContract $term)
    {
        $this->terms->put(
            $this->getTermKey($taxonomy, $slug),
            $term
        );
    }

    public function removeTerm($taxonomy, $slug)
    {
        $key = $this->getTermKey($taxonomy, $slug);

        $this->associations->forget($key);

        $this->titles->forget($key);

        $this->removeUri($taxonomy, $slug);
    }

    /**
     * Register a given term's URIs
     *
     * @param string $taxonomy  The taxonomy handle
     * @param string $slug      The term slug
     * @return void
     */
    public function addUris($taxonomy, $slug)
    {
        $defaultUri = $this->generateTermUri($taxonomy, $slug, $this->defaultLocale);

        // There will always be one URI regardless of whether any localization is enabled.
        $this->localizedUris($this->defaultLocale)->put(
            $this->getTermKey($taxonomy, $slug),
            $defaultUri
        );

        // Add URIs for each non-default locale. If the URI is the same as the
        // default, we'll simply ignore it since there's no reason to track it.
        foreach (Config::getOtherLocales() as $locale) {
            $uri = $this->generateTermUri($taxonomy, $slug, $locale);

            if ($uri === $defaultUri) {
                continue;
            }

            $this->localizedUris($locale)->put(
                $this->getTermKey($taxonomy, $slug),
                $uri
            );
        }
    }

    /**
     * Get the URIs in a given locale.
     *
     * @param string $locale
     * @return Collection
     */
    private function localizedUris($locale)
    {
        if (! $this->uris->has($locale)) {
            $this->uris->put($locale, collect());
        }

        return $this->uris->get($locale);
    }

    /**
     * Generate a term's URI.
     *
     * @param $taxonomy
     * @param $slug
     * @param $locale
     * @return mixed
     * @todo Populate the term with data in an efficient way, so that things other than slug will be available.
     */
    private function generateTermUri($taxonomy, $slug, $locale)
    {
        return $this->buildTermBySlug($slug, $taxonomy)->in($locale)->uri();
    }

    /**
     * Remove a URI
     *
     * @param string $taxonomy
     * @param string $slug
     */
    public function removeUri($taxonomy, $slug)
    {
        $key = $this->getTermKey($taxonomy, $slug);

        foreach ($this->uris as $locale => $terms) {
            $terms->forget($key);
        }
    }

    /**
     * Remove all localized URIs.
     *
     * (Leaves all URIs from the default locale in as-is)
     *
     * @return void
     */
    public function clearLocalizedUris()
    {
        $this->uris = $this->uris->take(1);
    }

    /**
     * Get a term by its slug (and taxonomy) if one exists
     *
     * @param string $slug      The term slug
     * @param string $taxonomy  The taxonomy handle
     * @return mixed|\Statamic\Contracts\Data\Content\Content
     */
    public function getTermBySlug($slug, $taxonomy)
    {
        if (! $this->slugExists($slug, $taxonomy)) {
            return;
        }

        $term = $this->buildTermBySlug($slug, $taxonomy);

        return $term;
    }

    /**
     * Build a term object by its slug (and taxonomy)
     *
     * A term object will be returned regardless of its existence.
     *
     * @param string $slug
     * @param string $taxonomy
     * @return \Statamic\Contracts\Data\Content\Content|TermContract
     */
    private function buildTermBySlug($slug, $taxonomy)
    {
        $key = $this->getTermKey($taxonomy, $slug);

        // Get the term if it's already been created this request.
        if ($this->terms->has($key)) {
            return $this->terms->get($key);
        }

        $term = Term::create($slug)->taxonomy($taxonomy)->get();

        $term = $this->addDataToTerm($term);

        // Save the term for this request.
        // Only save it if the Stache is ready enough. If not, we might be in a chicken and egg situation.
        // Terms are added to the Stache while entries are being added. When attempting to get entries
        // within a term, they may not all be loaded. When a collection of content is retrieved, it
        // gets cached on the Term object. We want to avoid caching a partial content collection.
        if (app(Stache::class)->isWarm()) {
            $this->terms->put($key, $term);
        }

        return $term;
    }

    /**
     * Check if a term exists based on its slug (and taxonomy)
     *
     * @param string $slug
     * @param string $taxonomy
     * @param bool $includeUnassociated
     * @return bool
     */
    public function slugExists($slug, $taxonomy, $includeUnassociated = true)
    {
        $key = $this->getTermKey($taxonomy, $slug);

        if (! $includeUnassociated) {
            return $this->associations->has($key);
        }

        $terms = $this->associations->keys()->merge(
            $this->getUnassociatedTermsInTaxonomy($taxonomy)->keys()
        );

        return $terms->contains($key);
    }

    private function addDataToTerm(TermContract $term)
    {
        if (File::disk('content')->exists($term->path())) {
            $term->data(
                YAML::parse(File::disk('content')->get($term->path()))
            );
        }

        foreach (Config::getOtherLocales($this->defaultLocale) as $locale) {
            if (! $term->has($locale)) {
                continue;
            }

            $term->dataForLocale($locale, $term->get($locale));
            $term->remove($locale);
        }

        return $term;
    }

    /**
     * Get all the terms
     *
     * @param bool $includeUnassociated   Whether to include un-associated terms
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function getTerms($includeUnassociated = true)
    {
        $associated = $this->getAssociatedTerms();

        if (! $includeUnassociated) {
            return $associated;
        }

        return $associated->merge(
            $this->getUnassociatedTerms()
        );
    }

    /**
     * Get the terms that have not been associated with data.
     *
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function getUnassociatedTerms()
    {
        $terms = collect_terms();

        foreach (Taxonomy::handles() as $taxonomy) {
            $terms = $terms->merge($this->getUnassociatedTermsInTaxonomy($taxonomy));
        }

        return $terms;
    }

    /**
     * Get the terms (optionally in a taxonomy) that have been associated with data.
     *
     * @param string $taxonomy The taxonomy handle
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function getAssociatedTerms($taxonomy = null)
    {
        $terms = $this->associations->map(function ($content, $key) {
            list($taxonomy, $slug) = explode('/', $key);
            return compact('taxonomy', 'slug');
        });

        if ($taxonomy) {
            $terms = $terms->filter(function ($term) use ($taxonomy) {
                return $term['taxonomy'] === $taxonomy;
            });
        }

        // Finally, we'll convert to actual term objects.
        return collect_terms($terms->map(function ($term) {
            return $this->buildTermBySlug($term['slug'], $term['taxonomy']);
        }));
    }

    /**
     * Get all the terms in a given taxonomy
     *
     * @param string|array $taxonomy      The taxonomy handle
     * @param bool $includeUnassociated   Whether to include un-associated terms
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function getTermsInTaxonomy($taxonomy, $includeUnassociated = true)
    {
        if (is_array($taxonomy)) {
            $ts = collect_terms();
            foreach ($taxonomy as $t) {
                $ts = $ts->merge($this->getTermsInTaxonomy($t, $includeUnassociated));
            }
            return $ts;
        }

        $associated = $this->getAssociatedTermsInTaxonomy($taxonomy);

        if (! $includeUnassociated) {
            return $associated;
        }

        return $associated->merge(
            $this->getUnassociatedTermsInTaxonomy($taxonomy)
        );
    }

    /**
     * Get the terms in a given taxonomy that have not been associated with data.
     *
     * @param string $taxonomy  The taxonomy handle
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function getUnassociatedTermsInTaxonomy($taxonomy)
    {
        if (! $taxonomy) {
            return collect_terms();
        }

        return collect_terms(
            Folder::disk('content')->getFilesByType("taxonomies/$taxonomy", 'yaml')
        )->map(function ($path) use ($taxonomy) {
            $slug = pathinfo($path)['filename'];
            $key = $taxonomy.'/'.$slug;
            return compact('path', 'key', 'slug');
        })->reject(function ($term) {
            return $this->associations->has($term['key']);
        })->keyBy('key')->map(function ($term) use ($taxonomy) {
            return $this->buildTermBySlug($term['slug'], $taxonomy);
        });
    }

    /**
     * Get the terms in a given taxonomy that have been associated with data.
     *
     * @param string $taxonomy            The taxonomy handle
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function getAssociatedTermsInTaxonomy($taxonomy)
    {
        return $this->getAssociatedTerms($taxonomy);
    }

    /**
     * Get the term URIs.
     *
     * @param string|null $locale  When provided, URIs for a specific locale will be returned.
     * @return Collection
     */
    public function uris($locale = null)
    {
        if (! $locale) {
            return $this->uris;
        }

        return $this->localizedUris($locale);
    }

    /**
     * Get the data in an array suitable for persisting between requests.
     *
     * @return string
     */
    public function toPersistableArray()
    {
        return json_encode([
            'associations' => $this->associations->toArray(),
            'uris' => $this->uris->toArray(),
            'titles' => $this->titles->toArray()
        ]);
    }

    /**
     * Load the data from a persisted array.
     *
     * @param string $persisted  An json object created by the toPersistableArray method.
     */
    public function load($persisted)
    {
        $persisted = json_decode($persisted, true);

        $this->associations = collect($persisted['associations'])->map(function ($associations, $key) {
            return collect($associations);
        });

        $this->uris = collect($persisted['uris'])->map(function ($uris, $locale) {
            return collect($uris);
        });

        $this->titles = collect($persisted['titles']);
    }

    /**
     * Get a normalized key used for identifying a term
     *
     * @param string $taxonomy  The taxonomy handle
     * @param string $term      The term slug
     * @return string
     */
    public function getTermKey($taxonomy, $term)
    {
        return $taxonomy . '/' . Term::normalizeSlug($term);
    }
}
