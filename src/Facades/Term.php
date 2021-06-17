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
 * @method static TermContract find($id)
 * @method static TermContract findByUri(string $uri, string $site = null)
 * @method static save($term)
 * @method static delete($term)
 * @method static TermQueryBuilder query()
 * @method static TermContract make(string $slug = null)
 * @method static int entriesCount(Term $term)
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
