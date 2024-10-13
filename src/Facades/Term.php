<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Contracts\Taxonomies\TermRepository;
use Statamic\Stache\Query\TermQueryBuilder;
use Statamic\Taxonomies\TermCollection;

/**
 * @method static TermCollection all()
 * @method static TermCollection whereTaxonomy(string $handle)
 * @method static TermCollection whereInTaxonomy(array $handles)
 * @method static null|TermContract find($id)
 * @method static null|TermContract findByUri(string $uri, string $site = null)
 * @method static TermContract findOrFail($id)
 * @method static TermContract make(string $slug = null)
 * @method static save($term)
 * @method static delete($term)
 * @method static TermQueryBuilder query()
 * @method static int entriesCount(Term $term)
 * @method static void substitute($item)
 * @method static \Illuminate\Support\Collection applySubstitutions($items)
 *
 * @see \Statamic\Contracts\Taxonomies\TermRepository
 * @see \Statamic\Taxonomies\Term
 */
class Term extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TermRepository::class;
    }
}
