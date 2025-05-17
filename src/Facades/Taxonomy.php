<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Taxonomies\TaxonomyRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Taxonomies\Taxonomy find($id)
 * @method static null|\Statamic\Contracts\Taxonomies\Taxonomy findByHandle($handle)
 * @method static \Statamic\Contracts\Taxonomies\Taxonomy findOrFail($id)
 * @method static null|\Statamic\Contracts\Taxonomies\Taxonomy findByUri(string $uri, string $site = null)
 * @method static \Illuminate\Support\Collection handles()
 * @method static bool handleExists(string $handle)
 * @method static void save(\Statamic\Contracts\Taxonomies\Taxonomy $taxonomy)
 * @method static void delete(\Statamic\Contracts\Taxonomies\Taxonomy $taxonomy)
 * @method static \Statamic\Contracts\Taxonomies\Taxonomy make(?string $handle = null)
 * @method static addPreviewTargets(string $handle, array $targets)
 * @method static additionalPreviewTargets(string $handle)
 *
 * @see \Statamic\Stache\Repositories\TaxonomyRepository
 * @link \Statamic\Taxonomies\Taxonomy
 */
class Taxonomy extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TaxonomyRepository::class;
    }
}
