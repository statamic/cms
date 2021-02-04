<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Taxonomies\TaxonomyRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Taxonomies\Taxonomy find($id)
 * @method static \Illuminate\Support\Collection handles()
 * @method static bool handleExists(string $handle)
 * @method static null|\Statamic\Contracts\Taxonomies\Taxonomy findByHandle($handle)
 * @method static void save(Taxonomy $taxonomy)
 * @method static void delete(Taxonomy $taxonomy)
 * @method static \Statamic\Contracts\Taxonomies\Taxonomy make(?string $handle = null)
 * @method static null|\Statamic\Contracts\Taxonomies\Taxonomy findByUri(string $uri, string $site = null)
 *
 * @see /Statamic\Stache\Repositories\TaxonomyRepository
 */
class Taxonomy extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TaxonomyRepository::class;
    }
}
