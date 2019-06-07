<?php

namespace Statamic\Contracts\Data\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Taxonomies\Term;

interface TermRepository
{
    public function all();
    public function whereTaxonomy(string $handle);
    public function whereInTaxonomy(array $handles);
    public function find($id);
    public function findByUri(string $uri);
    public function findBySlug(string $slug, string $collection);
    public function make();
    public function query();
    public function save($entry);
    public function delete($entry);
}
