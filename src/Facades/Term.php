<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Contracts\Taxonomies\TermRepository;
use Statamic\Stache\Query\TermQueryBuilder;
use Statamic\Taxonomies\TermCollection;

/**
 * @method static all(): TermCollection.
 * @method static whereTaxonomy(string $handle): TermCollection
 * @method static whereInTaxonomy(array $handles): TermCollection
 * @method static find($id): ?TermContract
 * @method static findByUri(string $uri, string $site = null): ?TermContract
 * @method static findBySlug(string $slug, string $taxonomy): ?TermContract
 * @method static save($term)
 * @method static delete($term)
 * @method static query(): TermQueryBuilder
 * @method static make(string $slug = null): Term
 * @method static entriesCount(Term $term): int
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
