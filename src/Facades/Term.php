<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Taxonomies\TermRepository;

/**
 * @method static all()
 * @method static whereTaxonomy(string $handle)
 * @method static whereInTaxonomy(array $handles)
 * @method static find($id)
 * @method static findByUri(string $uri)
 * @method static findBySlug(string $slug, string $taxonomy)
 * @method static make(string $slug = null)
 * @method static query()
 * @method static save($entry)
 * @method static delete($entry)
 *
 * @see \Statamic\Contracts\Taxonomies\TermRepository
 */
class Term extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TermRepository::class;
    }
}
